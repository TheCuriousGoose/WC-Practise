<?php

namespace App\Http\Controllers;

use App\Enums\Gamemode;
use App\Enums\Status;
use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LobbyController extends Controller
{
    public function index()
    {
        $lobbies = Lobby::query()
            ->with('players')
            ->where('status', Status::IN_LOBBY->value)
            ->paginate(5);

        return view('pages.lobbies.index', [
            'lobbies' => $lobbies
        ]);
    }

    public function create()
    {
        $games = Gamemode::values();
        $gamemodes = [];

        foreach ($games as $game) {
            $gamemodes[$game] = $game;
        }

        return view('pages.lobbies.create', [
            'gamemodes' => $gamemodes
        ]);
    }

    public function store(Request $request)
    {
        $validationFields = [
            'name' => ['required', 'max:255'],
            'is_private' => ['nullable', 'boolean'],
            'gamemode' => ['required', 'in:' . implode(',', Gamemode::values())],
            'max_players' => ['required', 'integer', 'min:2', 'max:10']
        ];

        if ($request->is_private) {
            $validationFields['password'] = ['required', 'min:8'];
        }

        $validated = $request->validate($validationFields);
        $validated['status'] = Status::IN_LOBBY->value;
        $validated['slug'] = Str::slug($validated['name']);

        $lobby = Lobby::create($validated);

        return redirect()->route('lobbies.show', $lobby);
    }

    public function show(Lobby $lobby, Request $request)
    {
        if ($lobby->is_private) {
            $validator = Validator::make($request->all(), [
                'password' => ['required']
            ]);

            if ($validator->fails() || !Hash::check($request->password, $lobby->password)) {
                return redirect()->back()->withErrors(['password' => 'Incorrect password'])->with('openModal', $lobby->slug);
            }
        }

        $lobby->load('players');

        $is_player = Session::get('is_player', false);

        if (!$is_player) {
            if ($lobby->players->count() + 1 > $lobby->max_players) {
                return redirect()->route('lobbies.index')->withErrors(['lobby' => 'Lobby is full']);
            }
        }


        return view('pages.lobbies.show', [
            'lobby' => $lobby
        ]);
    }

    public function submitUser(Request $request, Lobby $lobby)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'lobby_id' => ['required', 'exists:lobbies,id']
        ]);

        $player = $lobby->players()->create($validated);

        Session::put('is_player', true);

        return response()->json(['players' => $lobby->players, 'id' => $player->id]);
    }

    public function updates(Lobby $lobby, Request $request)
    {
        if ($request->has('drawing')) {
            $drawing = $request->input('drawing');
            $drawingData = str_replace('data:image/png;base64,', '', $drawing);
            $drawingData = str_replace(' ', '+', $drawingData);

            if ($lobby->drawing_path) {
                Storage::disk('public')->delete($lobby->drawing_path);
            }

            $filePath = $lobby->id . '/' . uniqid() . '.png';
            Storage::disk('public')->put($filePath, base64_decode($drawingData));

            $lobby->update(['drawing_path' => $filePath]);
        }

        return response()->json([
            'players' => $lobby->players,
            'status' => $lobby->status,
            'max_players' => $lobby->max_players,
            'player_with_turn' => $lobby->player_id_has_turn,
            'drawable_word' => $lobby->drawable_word,
            'drawing_url' => $lobby->drawing_path ? Storage::url($lobby->drawing_path) : null
        ]);
    }

    public function startGame(Lobby $lobby)
    {
        if ($lobby->gamemode == Gamemode::FREEHAND->value()) {
            $randomWord = $this->getRandomWord();
        }

        $lobby->load('players');

        $player = Player::where('lobby_id', $lobby->id)->inRandomOrder()->first();

        $lobby->playerHaveHadTurn()->create([
            'player_id' => $player->id
        ]);

        $lobby->update([
            'status' => Status::IN_GAME->value,
            'drawable_word' => $randomWord ?? null,
            'player_id_has_turn' => $player->id
        ]);

        return $this->updates($lobby, request());
    }

    public function getRandomWord()
    {
        $words = ["Dragon", "Pizza", "Rocket", "Unicorn", "Robot", "Castle", "Dinosaur", "Mermaid", "Superhero", "Spaceship"];
        return $words[array_rand($words)];
    }

    public function endTurn(Lobby $lobby, Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'drawing' => 'required|string',
        ]);

        $lobby->load('players');

        $drawingData = $request->input('drawing');
        $drawingData = str_replace('data:image/png;base64,', '', $drawingData);
        $drawingData = str_replace(' ', '+', $drawingData);

        $filename = $lobby->id . '/drawing_' . time() . '.png';

        Storage::disk('public')->put($filename, base64_decode($drawingData));

        $playersHaveHadTurn = $lobby->playerHaveHadTurn->pluck('player_id')->toArray();
        $player = Player::query()
            ->whereNotIn('id', $playersHaveHadTurn)
            ->where('lobby_id', $lobby->id)
            ->inRandomOrder()
            ->first();

        if ($player != null) {
            $lobby->playerHaveHadTurn()->create([
                'player_id' => $player->id,
                'drawing_filename' => $filename
            ]);

            $lobby->update([
                'player_id_has_turn' => $player->id
            ]);

            return $this->updates($lobby, request());
        }

        if (count($playersHaveHadTurn) === $lobby->max_players) {
            $lobby->update(['status' => Status::FINISHED->value]);

            return $this->updates($lobby, request());
        }

        return $this->updates($lobby, request());
    }
}
