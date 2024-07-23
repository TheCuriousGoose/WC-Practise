<div class="modal fade text-primary" id="passwordModal{{ $lobby->slug }}" tabindex="-1"
    aria-labelledby="passwordModal{{ $lobby->slug }}Label" aria-hidden="true">
    <form action="{{ route('lobbies.show', $lobby) }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    Enter lobby password
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control">
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary">Join</button>
                </div>
            </div>
        </div>
    </form>
</div>

@if (session('openModal') == $lobby->slug)
    <script type="module">
        let modal = new bootstrap.Modal('#passwordModal{{ $lobby->slug }}')
        modal.show();
    </script>
@endif
