class Lobby {
    constructor() {
        this.lobbyId = this.getLobbyId();
        this.playerId = null;
        this.playerBlock = document.getElementById('players');
        this.usernameModalElement = document.getElementById('usernameModal');
        this.saveUserButton = document.getElementById('saveUser');
        this.startButton = document.getElementById('startButton');
        this.maxPlayersElement = document.getElementById('maxPlayers');
        this.wordToDrawElement = document.getElementById('wordToDraw');
        this.playerCountElement = document.getElementById('playerCount');
        this.usernameForm = document.getElementById('usernameForm');
        this.usernameInput = document.getElementById('username');
        this.lobbyIdInput = document.getElementById('lobby_id');
        this.turnCountdownElement = document.getElementById('turnCountdown');
        this.drawingCanvas = document.getElementById('drawingArea');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.roundNumber = document.getElementById('round');
        this.drawingContext = this.drawingCanvas.getContext('2d');
        this.drawing = false;

        this.copyGameLink = document.getElementById('copyGameLink');

        this.overlay = document.getElementById('overlay');

        this.playerWithTurn = 0;
        this.playersLength = 0;
        this.imageLoadedForRound = false; // Flag to track image loading

        this.pollingInterval = null; // Store the interval ID

        this.firstInitialDrawing = true;

        this.init();
    }

    getLobbyId() {
        return window.location.href.substring(window.location.href.lastIndexOf('/') + 1);
    }

    init() {
        const player = localStorage.getItem('player.' + this.lobbyId);

        if (!player) {
            this.showUsernameModal();
        } else {
            this.playerId = JSON.parse(player).id;
            this.startPolling();
        }

        this.addEventListeners();
        this.initCanvas();
    }

    showUsernameModal() {
        const usernameModal = new bootstrap.Modal(this.usernameModalElement);
        usernameModal.show();
    }

    addEventListeners() {
        this.saveUserButton.addEventListener('click', () => {
            this.submitUsername();
        });

        this.startButton.addEventListener('click', () => {
            this.startGame();
        });

        this.copyGameLink.addEventListener('click', () => {
            this.copyGameLinkToClipboard();
        });
    }

    copyGameLinkToClipboard() {
        const gameLink = window.location.href;
        navigator.clipboard.writeText(gameLink)
            .then(() => {
                console.log('Game link copied to clipboard!');
            })
            .catch(err => {
                console.error('Failed to copy game link: ', err);
            });
    }

    submitUsername() {
        if (this.usernameForm.checkValidity()) {
            const username = this.usernameInput.value;
            const lobbyId = this.lobbyIdInput.value;

            this.sendRequest('submitPlayer', 'POST', {
                name: username,
                lobby_id: lobbyId
            }).then(data => {
                const usernameModal = bootstrap.Modal.getInstance(this.usernameModalElement);
                usernameModal.hide();

                this.playerId = data.id;

                localStorage.setItem('player.' + this.lobbyId, JSON.stringify({ id: this.playerId, name: username }));

                this.updateUI(data);
                this.startPolling();
            }).catch(error => {
                console.error('Error:', error);
            });
        } else {
            this.usernameForm.reportValidity();
        }
    }

    startGame() {
        this.sendRequest('startGame', 'POST', {})
            .then(data => {
                this.updateUI(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    startPolling() {
        this.pollingInterval = setInterval(() => {
            this.checkForUpdates();
        }, 500);
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    checkForUpdates() {
        let requestData = {
            player_id: this.playerId
        };

        // If it's the player's turn, include the drawing image in the request
        if (this.playerWithTurn === this.playerId) {
            const drawingDataUrl = this.drawingCanvas.toDataURL('image/png');
            requestData.drawing = drawingDataUrl;
        }

        this.sendRequest('checkForUpdates', 'POST', requestData)
            .then(data => {
                this.updateUI(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    sendRequest(endpointName, method, body) {
        const endpoint = document.querySelector(`[name="${endpointName}"]`).getAttribute('content');

        return fetch(endpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        }).then(response => response.json());
    }

    startGameEnd() {
        this.stopPolling();
        this.startButton.disabled = true;
        this.startButton.innerHTML = 'Game Over';

        this.showFinalImages();
    }

    showFinalImages() {
        this.sendRequest('getImages', 'POST', {})
            .then(images => {
                this.overlay.classList.add('show');

                this.loopThroughImages(images);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    loopThroughImages(images) {
        console.log(images);

        let currentIndex = 0;
        setInterval(() => {
            if (images.length > 0) {
                this.overlay.querySelector('img').src = images[currentIndex];
                currentIndex = (currentIndex + 1) % images.length;
            }
        }, 3000);
    }

    updateUI(data) {
        this.maxPlayersElement.innerHTML = data.max_players;
        this.wordToDrawElement.innerHTML = data.drawable_word;

        if (data.current_round > this.roundNumber.innerHTML) {
            this.roundNumber.innerHTML = data.current_round;
            this.imageLoadedForRound = false;
        }

        if (data.status === 'finished') {
            this.startGameEnd();
            return;
        } else if (data.status !== 'in_lobby') {
            this.startButton.style.display = 'none';
        } else {
            this.startButton.style.display = 'block';
        }

        if (data.player_with_turn != null) {
            if (this.playerWithTurn !== data.player_with_turn) {
                this.playerWithTurn = data.player_with_turn;
                this.highlightPlayerWithTurn();

                if (this.playerWithTurn == this.playerId) {
                    this.startTurnCountdown();
                }
            }
        }

        if (data.players) {
            this.startButton.disabled = data.players.length < data.max_players;
            this.playerCountElement.innerHTML = data.players.length;

            if (this.playersLength !== data.players.length) {
                this.playerBlock.innerHTML = '';
                data.players.forEach(player => {
                    let extraClasses = '';

                    if (player.id == this.playerId) {
                        extraClasses = 'border-3 border-secondary';
                    }

                    this.playerBlock.innerHTML += `<div class="card mb-2 ${extraClasses}" data-player-id="${player.id}"><div class="card-body">${player.name}</div></div>`;
                });
                this.playersLength = data.players.length;
            }
        }

        if (!this.imageLoadedForRound && data.random_image != null) {
            const image = new Image();
            image.src = data.random_image;

            image.onload = () => {
                this.drawingContext.clearRect(0, 0, this.drawingCanvas.width, this.drawingCanvas.height);
                this.drawingContext.drawImage(image, 0, 0, this.drawingCanvas.width, this.drawingCanvas.height);
                this.imageLoadedForRound = true;
            };
        }

        this.renderDrawingOnCanvas(data.drawing_url);
    }

    renderDrawingOnCanvas(imageUrl) {
        if (imageUrl && (this.playerWithTurn !== this.playerId)) {

            const image = new Image();
            image.src = imageUrl;

            image.onload = () => {
                // Clear previous drawing
                this.drawingContext.clearRect(0, 0, this.drawingCanvas.width, this.drawingCanvas.height);

                // Draw the new image
                this.drawingContext.drawImage(image, 0, 0, this.drawingCanvas.width, this.drawingCanvas.height);
            };
        }
    }

    highlightPlayerWithTurn() {
        const previousPlayerElement = document.querySelector('.border-success');
        if (previousPlayerElement) {
            previousPlayerElement.classList.remove('border-success', 'border-3');
        }

        const currentPlayerElement = document.querySelector(`[data-player-id="${this.playerWithTurn}"]`);
        if (currentPlayerElement) {
            currentPlayerElement.classList.add('border-success', 'border-3');
        }
    }

    startTurnCountdown() {
        let countdown = 3;
        this.turnCountdownElement.innerHTML = `Your turn starts in: ${countdown}`;
        this.turnCountdownElement.style.display = 'block';

        const countdownInterval = setInterval(() => {
            countdown -= 1;
            this.turnCountdownElement.innerHTML = `Your turn starts in: ${countdown}`;

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                this.turnCountdownElement.innerHTML = 'Your turn!';
                this.startDrawingCountdown();
            }
        }, 1000);
    }

    startDrawingCountdown() {
        let drawingTime = 8;
        this.turnCountdownElement.innerHTML = `Time left: ${drawingTime}`;

        this.enableDrawing();

        const drawingInterval = setInterval(() => {
            drawingTime -= 1;
            this.turnCountdownElement.innerHTML = `Time left: ${drawingTime}`;

            if (drawingTime <= 0) {
                clearInterval(drawingInterval);
                this.turnCountdownElement.style.display = 'none';
                this.endTurn();
            }
        }, 1000);
    }

    enableDrawing() {
        this.drawingCanvas.addEventListener('mousedown', this.startDrawing.bind(this));
        this.drawingCanvas.addEventListener('mousemove', this.draw.bind(this));
        this.drawingCanvas.addEventListener('mouseup', this.stopDrawing.bind(this));
        this.drawingCanvas.addEventListener('mouseout', this.stopDrawing.bind(this));
    }

    disableDrawing() {
        this.drawing = false;

        const canvasImage = this.drawingCanvas.toDataURL('image/png');
        const newCanvas = this.drawingCanvas.cloneNode(true);

        this.drawingCanvas.replaceWith(newCanvas);

        this.drawingCanvas = newCanvas;
        this.drawingContext = this.drawingCanvas.getContext('2d');

        this.initCanvas();

        const image = new Image();
        image.src = canvasImage;
        image.onload = () => {
            this.drawingContext.drawImage(image, 0, 0);
        };
    }

    startDrawing(e) {
        this.drawing = true;
        this.draw(e);
    }

    draw(e) {
        if (!this.drawing) return;
        this.drawingContext.lineWidth = 5;
        this.drawingContext.lineCap = 'round';
        this.drawingContext.strokeStyle = 'black';

        const rect = this.drawingCanvas.getBoundingClientRect();
        this.drawingContext.lineTo(e.clientX - rect.left, e.clientY - rect.top);
        this.drawingContext.stroke();
        this.drawingContext.beginPath();
        this.drawingContext.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }

    stopDrawing() {
        this.drawing = false;
        this.drawingContext.beginPath();
    }

    endTurn() {

        const drawingDataUrl = this.drawingCanvas.toDataURL('image/png');
        this.disableDrawing();

        this.sendRequest('endTurn', 'POST', {
            player_id: this.playerId,
            drawing: drawingDataUrl
        }).then(data => {
            this.updateUI(data);
        }).catch(error => {
            console.error('Error:', error);
        });
    }

    initCanvas() {
        this.drawingCanvas.width = this.drawingCanvas.offsetWidth;
        this.drawingCanvas.height = this.drawingCanvas.offsetHeight;
    }
}

export default Lobby;
