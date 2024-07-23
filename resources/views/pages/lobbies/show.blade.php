@extends('layouts.app')

@section('content')
    <div class="container min-vh-100">
        <div class="row pt-5">
            <div class="col-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="card-title">
                            {{ $lobby->name }}
                        </div>
                        <div class="ms-auto">
                            <span id="playerCount">{{ $lobby->players->count() }}</span>
                            /
                            <span id="maxPlayers">{{ $lobby->max_players }}</span>
                        </div>
                    </div>
                    <div class="card-body" id="players">
                        @foreach ($lobby->players as $player)
                            <div class="card mb-2" data-player-id="{{ $player->id }}">
                                <div class="card-body">
                                    {{ $player->name }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button id="startButton" class="btn btn-secondary" disabled>
                            Start game
                        </button>
                        <button id="copyGameLink" class="btn btn-secondary">
                            Click to copy game link
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-8">
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center w-100">
                        <h3>Round <span id="round">1</span> / {{ $lobby->rounds }}</h3>
                        <div>The word is <span class="ms-1" id="wordToDraw">...</span></div>
                        <span id="turnCountdown"></span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            Canvas
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="drawingArea" class="w-100" style="height: 40rem !important;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed w-100 min-vh-100 bg-overlay top-0 left-0 justify-content-center align-items-center"
        id="overlay">
        <div class="card text-primary w-50">
            <div class="card-body">
                <h1>Game End</h1>
                <img src="" id="image-display" class="object-fit-contain w-100">
            </div>
        </div>
    </div>
    <div class="modal fade" id="usernameModal" data-bs-backdrop='static' tabindex="-1" aria-labelledby="usernameModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content text-primary">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="usernameModalLabel">Enter Your Username</h1>
                </div>
                <div class="modal-body">
                    <form id="usernameForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <input type="hidden" name="lobby_id" id="lobby_id" value="{{ $lobby->id }}">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveUser">Save Username</button>
                </div>
            </div>
        </div>
    </div>
    <meta name="submitPlayer" content="{{ route('lobbies.submit-user', $lobby) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="checkForUpdates" content="{{ route('lobbies.get-updates', $lobby) }}">
    <meta name="startGame" content="{{ route('lobbies.start-game', $lobby) }}">
    <meta name="endTurn" content="{{ route('lobbies.end-turn', $lobby) }}">
    <meta name="getImages" content="{{ route('lobbies.get-images', $lobby) }}">
@endsection

@push('footer-scripts')
    @vite(['resources/js/show.js'])
@endpush
