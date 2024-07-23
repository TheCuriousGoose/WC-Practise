@extends('layouts.app')

@section('content')
    <nav class="navbar">
        <div class="container my-5">
            <div class="d-flex justify-content-center gap-5 w-100">
                <div class="nav-item">
                    <a href="">How to play</a>
                </div>
                <div class="nav-item">
                    <a href="">About us</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="container mt-5 pt-5 d-flex justify-content-center align-items-center flex-column">
        <p class="fs-lg unleash mb-2">Unleash your creativity</p>
        <p class="">widthin</p>
        <h1 class="fs-xxl">8 SECONDS</h1>
        <br>
        <p class="mb-5">Join or create a drawing game and let your imagination run wild!</p>
        <a href="{{ route('lobbies.create') }}" class="btn btn-primary mb-3">
            CREATE NEW GAME
        </a>
        <a href="{{ route('lobbies.index') }}" class="btn btn-secondary">
            JOIN A GAME
        </a>
    </main>
@endsection
