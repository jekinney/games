<template>
  <div class="memory-game-container">
    <!-- Game Header -->
    <div class="game-header">
      <h2 class="game-title">Memory Test Game</h2>
      <div class="game-stats">
        <div class="stat">
          <span class="stat-label">Score:</span>
          <span class="stat-value">{{ score }}</span>
        </div>
        <div class="stat">
          <span class="stat-label">Level:</span>
          <span class="stat-value">{{ level }}</span>
        </div>
        <div class="stat">
          <span class="stat-label">Lives:</span>
          <span class="stat-value">{{ lives }}</span>
        </div>
      </div>
    </div>

    <!-- Game Board -->
    <div class="game-board">
      <div 
        v-for="(card, index) in cards" 
        :key="index"
        :class="[
          'memory-card',
          { 
            'flipped': card.isFlipped || card.isMatched,
            'matched': card.isMatched,
            'disabled': isAnimating || gameOver
          }
        ]"
        @click="flipCard(index)"
      >
        <div class="card-inner">
          <div class="card-front">
            <div class="card-pattern"></div>
          </div>
          <div class="card-back">
            <div class="card-symbol">{{ card.symbol }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Messages -->
    <div class="game-messages">
      <div v-if="gameMessage" class="message" :class="messageType">
        {{ gameMessage }}
      </div>
    </div>

    <!-- Game Controls -->
    <div class="game-controls">
      <button 
        v-if="!gameStarted" 
        @click="startGame" 
        class="btn btn-primary"
      >
        Start Game
      </button>
      <button 
        v-if="gameStarted && !gameOver" 
        @click="pauseGame" 
        class="btn btn-secondary"
      >
        {{ isPaused ? 'Resume' : 'Pause' }}
      </button>
      <button 
        v-if="gameOver" 
        @click="restartGame" 
        class="btn btn-primary"
      >
        Play Again
      </button>
      <button 
        @click="resetGame" 
        class="btn btn-danger"
      >
        Reset
      </button>
    </div>

    <!-- How to Play -->
    <div class="how-to-play">
      <h3>How to Play:</h3>
      <ul>
        <li>Click cards to flip them over and reveal symbols</li>
        <li>Find matching pairs of symbols</li>
        <li>Match all pairs to advance to the next level</li>
        <li>Each level adds more cards and increases difficulty</li>
        <li>You lose a life for each wrong match</li>
        <li>Game ends when you run out of lives</li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface Card {
  id: number;
  symbol: string;
  isFlipped: boolean;
  isMatched: boolean;
}

// Game state
const cards = ref<Card[]>([]);
const flippedCards = ref<number[]>([]);
const score = ref(0);
const level = ref(1);
const lives = ref(3);
const gameStarted = ref(false);
const gameOver = ref(false);
const isPaused = ref(false);
const isAnimating = ref(false);
const gameMessage = ref('');
const messageType = ref('');

// Game symbols for different levels
const symbols = ['🎮', '🎯', '🎪', '🎨', '🎭', '🎺', '🎸', '🎹', '🎲', '🃏', '🎳', '🎱', '🏆', '🏅', '🏀', '⚽', '🎾', '🏈', '🏐', '🏓'];

// Computed properties
const cardCount = computed(() => {
  return Math.min(4 + (level.value - 1) * 2, 20); // Start with 4 cards, add 2 each level, max 20
});

const pairsCount = computed(() => cardCount.value / 2);

// Game functions
const generateCards = (): Card[] => {
  const selectedSymbols = symbols.slice(0, pairsCount.value);
  const cardSymbols = [...selectedSymbols, ...selectedSymbols];
  
  // Shuffle the symbols
  for (let i = cardSymbols.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [cardSymbols[i], cardSymbols[j]] = [cardSymbols[j], cardSymbols[i]];
  }

  return cardSymbols.map((symbol, index) => ({
    id: index,
    symbol,
    isFlipped: false,
    isMatched: false
  }));
};

const startGame = () => {
  gameStarted.value = true;
  gameOver.value = false;
  isPaused.value = false;
  cards.value = generateCards();
  showMessage('Game Started! Find the matching pairs!', 'success');
};

const pauseGame = () => {
  isPaused.value = !isPaused.value;
  showMessage(isPaused.value ? 'Game Paused' : 'Game Resumed', 'info');
};

const resetGame = () => {
  gameStarted.value = false;
  gameOver.value = false;
  isPaused.value = false;
  score.value = 0;
  level.value = 1;
  lives.value = 3;
  cards.value = [];
  flippedCards.value = [];
  showMessage('Game Reset', 'info');
};

const restartGame = () => {
  gameOver.value = false;
  lives.value = 3;
  score.value = 0;
  level.value = 1;
  startGame();
};

const flipCard = (index: number) => {
  if (isPaused.value || isAnimating.value || gameOver.value || !gameStarted.value) return;
  
  const card = cards.value[index];
  if (card.isFlipped || card.isMatched) return;

  card.isFlipped = true;
  flippedCards.value.push(index);

  if (flippedCards.value.length === 2) {
    checkMatch();
  }
};

const checkMatch = () => {
  isAnimating.value = true;
  
  setTimeout(() => {
    const [firstIndex, secondIndex] = flippedCards.value;
    const firstCard = cards.value[firstIndex];
    const secondCard = cards.value[secondIndex];

    if (firstCard.symbol === secondCard.symbol) {
      // Match found
      firstCard.isMatched = true;
      secondCard.isMatched = true;
      score.value += 10 * level.value;
      showMessage('Match found! +' + (10 * level.value) + ' points', 'success');
      
      // Check if level completed
      if (cards.value.every(card => card.isMatched)) {
        levelComplete();
      }
    } else {
      // No match
      firstCard.isFlipped = false;
      secondCard.isFlipped = false;
      lives.value--;
      showMessage('No match! Lives remaining: ' + lives.value, 'error');
      
      if (lives.value <= 0) {
        endGame();
      }
    }

    flippedCards.value = [];
    isAnimating.value = false;
  }, 1000);
};

const levelComplete = () => {
  level.value++;
  score.value += 50; // Bonus for completing level
  showMessage(`Level ${level.value - 1} Complete! Bonus: 50 points. Starting Level ${level.value}!`, 'success');
  
  setTimeout(() => {
    cards.value = generateCards();
  }, 2000);
};

const endGame = () => {
  gameOver.value = true;
  showMessage(`Game Over! Final Score: ${score.value}`, 'error');
};

const showMessage = (message: string, type: string) => {
  gameMessage.value = message;
  messageType.value = type;
  
  setTimeout(() => {
    gameMessage.value = '';
    messageType.value = '';
  }, 3000);
};

// Lifecycle
onMounted(() => {
  // Initialize empty game board
  cards.value = [];
});

onUnmounted(() => {
  // Cleanup if needed
});
</script>

<style scoped>
.memory-game-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Arial', sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 15px;
  color: white;
  min-height: 600px;
}

.game-header {
  text-align: center;
  margin-bottom: 20px;
}

.game-title {
  font-size: 2.5rem;
  margin-bottom: 15px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.game-stats {
  display: flex;
  justify-content: center;
  gap: 30px;
  margin-bottom: 20px;
}

.stat {
  background: rgba(255,255,255,0.2);
  padding: 10px 20px;
  border-radius: 10px;
  backdrop-filter: blur(10px);
}

.stat-label {
  font-weight: bold;
  margin-right: 5px;
}

.stat-value {
  color: #ffd700;
  font-weight: bold;
  font-size: 1.2rem;
}

.game-board {
  display: grid;
  gap: 15px;
  margin-bottom: 20px;
  justify-content: center;
  grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

.memory-card {
  width: 80px;
  height: 80px;
  perspective: 1000px;
  cursor: pointer;
  transition: transform 0.2s;
}

.memory-card:hover:not(.disabled) {
  transform: scale(1.05);
}

.memory-card.disabled {
  cursor: not-allowed;
}

.card-inner {
  width: 100%;
  height: 100%;
  position: relative;
  transform-style: preserve-3d;
  transition: transform 0.6s;
}

.memory-card.flipped .card-inner {
  transform: rotateY(180deg);
}

.card-front, .card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.card-front {
  background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%);
  border: 3px solid #fff;
}

.card-back {
  background: linear-gradient(45deg, #fa709a 0%, #fee140 100%);
  border: 3px solid #fff;
  transform: rotateY(180deg);
}

.card-pattern {
  width: 30px;
  height: 30px;
  background: repeating-linear-gradient(
    45deg,
    rgba(255,255,255,0.3),
    rgba(255,255,255,0.3) 5px,
    transparent 5px,
    transparent 10px
  );
  border-radius: 50%;
}

.card-symbol {
  font-size: 2rem;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.memory-card.matched .card-back {
  background: linear-gradient(45deg, #56ab2f 0%, #a8e6cf 100%);
  animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
  0% { transform: rotateY(180deg) scale(1); }
  50% { transform: rotateY(180deg) scale(1.1); }
  100% { transform: rotateY(180deg) scale(1); }
}

.game-messages {
  text-align: center;
  margin-bottom: 20px;
  min-height: 30px;
}

.message {
  padding: 10px 20px;
  border-radius: 25px;
  font-weight: bold;
  backdrop-filter: blur(10px);
  display: inline-block;
  animation: slideIn 0.3s ease-out;
}

.message.success {
  background: rgba(76, 175, 80, 0.8);
  color: white;
}

.message.error {
  background: rgba(244, 67, 54, 0.8);
  color: white;
}

.message.info {
  background: rgba(33, 150, 243, 0.8);
  color: white;
}

@keyframes slideIn {
  from { transform: translateY(-20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.game-controls {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.btn-primary {
  background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
}

.btn-danger {
  background: linear-gradient(45deg, #ff6b6b 0%, #ee5a24 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.btn:active {
  transform: translateY(0);
}

.how-to-play {
  background: rgba(255,255,255,0.1);
  padding: 20px;
  border-radius: 15px;
  backdrop-filter: blur(10px);
  margin-top: 20px;
}

.how-to-play h3 {
  margin-bottom: 15px;
  color: #ffd700;
}

.how-to-play ul {
  list-style: none;
  padding: 0;
}

.how-to-play li {
  padding: 5px 0;
  padding-left: 20px;
  position: relative;
}

.how-to-play li:before {
  content: "🎮";
  position: absolute;
  left: 0;
}

/* Responsive design */
@media (max-width: 600px) {
  .memory-game-container {
    padding: 15px;
  }
  
  .game-title {
    font-size: 2rem;
  }
  
  .game-stats {
    flex-direction: column;
    gap: 10px;
  }
  
  .memory-card {
    width: 60px;
    height: 60px;
  }
  
  .card-symbol {
    font-size: 1.5rem;
  }
  
  .game-controls {
    flex-direction: column;
    align-items: center;
  }
  
  .btn {
    width: 200px;
  }
}
</style>
