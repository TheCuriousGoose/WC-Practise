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
