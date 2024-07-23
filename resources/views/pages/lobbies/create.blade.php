@extends('layouts.app')

@section('content')
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <form action="{{ route('lobbies.store') }}" method="post" class="card w-50">
            @csrf
            <div class="card-header">
                <div class="card-title">
                    Create lobby
                </div>
            </div>
            <div class="card-body">
                <x-inputs.text id="name" name="name" label="Name" extras="required" />
                <x-inputs.checkbox id="is_private" :checked="true" name="is_private" label="Private game" />
                <div id="password-container">
                    <x-inputs.text id="password" name="password" label="Password" />
                </div>
                <x-inputs.radio id="gamemode" name="gamemode" label="Gamemode" :options="$gamemodes" />
                <x-inputs.select id="max_players" name="max_players" label="Max players" :options="array_combine(range(2, 10), range(2, 10))" />
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-secondary">
                    Create game
                </button>
            </div>
        </form>
    </div>
@endsection

@push('footer-scripts')
    <script>
        document.getElementById('is_private').addEventListener('change', (e) => {
            let checkbox = e.target.closest('input');

            console.log(checkbox.checked);

            if (checkbox.checked) {
                document.getElementById('password-container').style.display = 'block';
            } else {
                document.getElementById('password-container').style.display = 'none';
            }
        })
    </script>
@endpush
