@extends('layouts.app')

@section('content')
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card w-50">
            <div class="card-header">
                <div class="card-title">
                    Lobbies
                </div>
            </div>
            <div class="card-body">
                @foreach ($lobbies as $lobby)
                    <div class="card mb-2">
                        <div class="card-body row d-flex align-items-center">
                            @if ($lobby->is_private)
                                <div class="col-auto">
                                    <i class="fa fa-lock" data-bs-toggle="tooltip" title="Private"></i>
                                </div>
                            @endif
                            <div class="col-4">
                                <p class="fs-lg fw-bold text-primary">{{ $lobby->name }}</p>
                            </div>
                            <div class="col-2 d-flex justify-content-center ms-auto">
                                {{ count($lobby->players) }} / {{ $lobby->max_players }}
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <a class="btn btn-secondary"
                                    @if ($lobby->is_private) data-bs-toggle="modal"
                                    data-bs-target="#passwordModal{{ $lobby->slug }}"
                                @else
                                    href="{{ route('lobbies.show', $lobby) }}" @endif>
                                    Join
                                </a>
                            </div>
                        </div>
                    </div>
                    @if ($lobby->is_private)
                        @push('footer-scripts')
                            <x-lobby-password :lobby="$lobby" />
                        @endpush
                    @endif
                @endforeach
                <div class="mt-3">
                    {{ $lobbies->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
