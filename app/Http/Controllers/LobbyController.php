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
            'max_players' => ['required', 'integer', 'min:2', 'max:10'],
            'rounds' => ['required', 'integer', 'min:1', 'max:10'],
        ];

        if ($request->is_private) {
            $validationFields['password'] = ['required', 'min:8'];
        }

        $validated = $request->validate($validationFields);
        $validated['status'] = Status::IN_LOBBY->value;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['current_round'] = 1;

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

            $filePath = $lobby->id . '/' . uniqid() . '.png';
            Storage::disk('public')->put($filePath, base64_decode($drawingData));

            $lobby->update(['drawing_path' => $filePath]);
        }

        $drawUrl = null;

        if($lobby->drawing_path){
            if($lobby->gamemode == Gamemode::RETRACE->value() && str_contains($lobby->drawing_path, 'svg')){
                $drawUrl = $lobby->drawing_path;
            }else {
                $drawUrl = Storage::url($lobby->drawing_path);
            }
        }


        return response()->json([
            'players' => $lobby->players,
            'status' => $lobby->status,
            'gamemode' => $lobby->gamemode,
            'max_players' => $lobby->max_players,
            'player_with_turn' => $lobby->player_id_has_turn,
            'drawable_word' => $lobby->drawable_word,
            'drawing_url' => $drawUrl,
            'current_round' => $lobby->current_round,
            'random_image' => $lobby->random_image
        ]);
    }

    public function startGame(Lobby $lobby)
    {
        $randomImage = null;

        if ($lobby->gamemode == Gamemode::FREEHAND->value()) {
            $randomWord = $this->getRandomWord();
        } else if ($lobby->gamemode == Gamemode::RETRACE->value()) {
            $randomWord = '';
            $randomImage = asset('imgs/retrace/' . rand(1, 10) . '.svg');
        }

        $lobby->load('players');

        $player = Player::where('lobby_id', $lobby->id)->inRandomOrder()->first();

        $lobby->playerHaveHadTurn()->create([
            'player_id' => $player->id,
            'round' => $lobby->current_round
        ]);

        $lobby->update([
            'status' => Status::IN_GAME->value,
            'drawable_word' => $randomWord ?? null,
            'player_id_has_turn' => $player->id,
            'random_image' => $randomImage
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

        // Decode the drawing data and save it
        $drawingData = $request->input('drawing');
        $drawingData = str_replace('data:image/png;base64,', '', $drawingData);
        $drawingData = str_replace(' ', '+', $drawingData);

        $filename = $lobby->id . '/' . $lobby->player_id_has_turn . '/round' . $lobby->current_round . '.png';
        Storage::disk('public')->put($filename, base64_decode($drawingData));

        $playersHaveHadTurn = $lobby->playerHaveHadTurn->pluck('player_id')->toArray();

        $nextPlayer = Player::query()
            ->whereNotIn('id', $playersHaveHadTurn)
            ->where('lobby_id', $lobby->id)
            ->inRandomOrder()
            ->first();

        if ($nextPlayer !== null) {
            // Assign the turn to the next player
            $lobby->playerHaveHadTurn()->create([
                'player_id' => $nextPlayer->id,
                'round' => $lobby->current_round
            ]);

            $lobby->update([
                'player_id_has_turn' => $nextPlayer->id
            ]);

            return $this->updates($lobby, $request);
        }

        if (count($playersHaveHadTurn) === $lobby->max_players) {
            if ($lobby->current_round + 1 <= $lobby->rounds) {
                $lobby->update([
                    'current_round' => $lobby->current_round + 1,
                    'player_id_has_turn' => null,
                    'drawable_word' => null,
                    'drawing_path' => null
                ]);

                $playersHaveHadTurns = $lobby->playerHaveHadTurn;

                foreach($playersHaveHadTurns as $playerHaveHadTurn){
                    $playerHaveHadTurn->delete();
                }

                return $this->startGame($lobby);
            } else {
                // No more rounds left, finish the game
                $lobby->update(['status' => Status::FINISHED->value]);
            }
        }

        return $this->updates($lobby, $request);
    }

    public function getImages(Lobby $lobby)
    {
        // Retrieve all files from the lobby's directory and its subdirectories
        $files = Storage::disk('public')->allFiles($lobby->id);

        $imageUrls = array_filter($files, function ($file) {
            return str_contains($file, 'round');
        });

        $imgsToDelete = array_diff($files, $imageUrls);
        Storage::disk('public')->delete($imgsToDelete);

        $imageUrls = array_map(function ($file) {
            return Storage::disk('public')->url($file);
        }, $imageUrls);

        return response()->json($imageUrls);
    }
}
