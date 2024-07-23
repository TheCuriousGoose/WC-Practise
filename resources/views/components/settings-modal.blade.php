<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-primary">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="usernameModalLabel">Settings</h1>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Setting name</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gamemode</td>
                            <td>{{ $lobby->gamemode }}</td>
                        </tr>
                        <tr>
                            <td>Max players</td>
                            <td>{{ $lobby->max_players }}</td>
                        </tr>
                        <tr>
                            <td>Private</td>
                            <td>{{ $lobby->is_private ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td>Rounds</td>
                            <td>{{ $lobby->rounds}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
