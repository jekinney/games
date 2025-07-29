import { ref, onMounted, onUnmounted } from 'vue';

export interface GamePlayer {
    user_id?: number;
    user_name: string;
    session_id: string;
    started_at: string;
    last_seen?: string;
}

export interface ScoreUpdate {
    user_id?: number;
    user_name: string;
    score: number;
    rank: number;
    game_data?: any;
    timestamp: string;
}

export function useRealTimeGame(gameSlug: string) {
    const activePlayers = ref<GamePlayer[]>([]);
    const activePlayersCount = ref(0);
    const recentScores = ref<ScoreUpdate[]>([]);
    const sessionId = ref<string>('');
    const isSessionActive = ref(false);
    
    let heartbeatInterval: number | null = null;
    let leaderboardChannel: any = null;
    let playersChannel: any = null;

    // Helper functions
    const getUserId = () => {
        // Get user ID from global auth object or return null
        return (window as any)?.Laravel?.user?.id || null;
    };

    const getUserName = () => {
        // Get user name from global auth object or return Guest
        return (window as any)?.Laravel?.user?.name || 'Guest';
    };

    // Start game session using WebSocket events only
    const startGameSession = async () => {
        try {
            // Generate session ID if not already set
            if (!sessionId.value) {
                sessionId.value = Math.random().toString(36).substr(2, 9);
            }
            
            // Connect to the players channel
            playersChannel = window.Echo.channel(`game.${gameSlug}.players`);
            
            // Listen for other players
            startPlayersListener();
            
            // Broadcast game start via WebSocket
            playersChannel.whisper('game.started', {
                session_id: sessionId.value,
                user_id: getUserId(),
                user_name: getUserName(),
                timestamp: Date.now()
            });
            
            // Start heartbeat for connection maintenance
            startHeartbeat();
            
            isSessionActive.value = true;
            
            return {
                success: true,
                session_id: sessionId.value,
                message: 'Game session started via WebSocket'
            };
        } catch (error) {
            console.error('Error starting game session:', error);
            return {
                success: false,
                error: error instanceof Error ? error.message : 'Unknown error'
            };
        }
    };

    // End game session using WebSocket events
    const endGameSession = async (score?: number, timePlayed?: number) => {
        if (!sessionId.value) return;
        
        try {
            // Broadcast game end via WebSocket
            if (playersChannel) {
                playersChannel.whisper('game.ended', {
                    session_id: sessionId.value,
                    user_id: getUserId(),
                    user_name: getUserName(),
                    score: score || 0,
                    time_played: timePlayed || 0,
                    timestamp: Date.now()
                });
            }
            
            // Stop heartbeat
            stopHeartbeat();
            
            // Clean up channels
            if (playersChannel) {
                window.Echo.leave(`game.${gameSlug}.players`);
                playersChannel = null;
            }
            
            if (leaderboardChannel) {
                window.Echo.leave(`game.${gameSlug}.leaderboard`);
                leaderboardChannel = null;
            }
            
            sessionId.value = '';
            isSessionActive.value = false;
            
            console.log(`Game session ended for ${gameSlug}`);
        } catch (error) {
            console.error('Failed to end game session:', error);
        }
    };

    // Submit score using WebSocket events instead of API
    const submitScore = async (scoreData: {
        score: number;
        level_reached?: number;
        time_played_seconds?: number;
        game_data?: any;
    }) => {
        try {
            // Connect to leaderboard channel if not already connected
            if (!leaderboardChannel) {
                leaderboardChannel = window.Echo.channel(`game.${gameSlug}.leaderboard`);
            }
            
            // Submit score via WebSocket whisper
            leaderboardChannel.whisper('score.submit', {
                session_id: sessionId.value,
                user_id: getUserId(),
                user_name: getUserName(),
                score: scoreData.score,
                level_reached: scoreData.level_reached || 1,
                time_played_seconds: scoreData.time_played_seconds || 0,
                game_data: scoreData.game_data || {},
                timestamp: Date.now()
            });
            
            // Also end the game session
            await endGameSession(scoreData.score, scoreData.time_played_seconds);
            
            return {
                success: true,
                message: 'Score submitted via WebSocket'
            };
        } catch (error) {
            console.error('Error submitting score:', error);
            return {
                success: false,
                error: error instanceof Error ? error.message : 'Unknown error'
            };
        }
    };

    // Heartbeat for connection maintenance (WebSocket only)
    const sendHeartbeat = async () => {
        if (!sessionId.value || !playersChannel) return;
        
        try {
            // Send heartbeat via WebSocket whisper
            playersChannel.whisper('game.heartbeat', {
                session_id: sessionId.value,
                user_id: getUserId(),
                timestamp: Date.now()
            });
        } catch (error) {
            console.error('Failed to send heartbeat:', error);
        }
    };

    const startHeartbeat = () => {
        heartbeatInterval = setInterval(sendHeartbeat, 30000); // Every 30 seconds
    };

    const stopHeartbeat = () => {
        if (heartbeatInterval) {
            clearInterval(heartbeatInterval);
            heartbeatInterval = null;
        }
    };

    // Listen for other players and game events
    const startPlayersListener = () => {
        if (!playersChannel) return;

        // Listen for players joining
        playersChannel.listenForWhisper('game.started', (data: any) => {
            console.log('Player joined:', data);
            // Update active players count
            activePlayersCount.value += 1;
        });

        // Listen for players leaving
        playersChannel.listenForWhisper('game.ended', (data: any) => {
            console.log('Player left:', data);
            // Update active players count
            activePlayersCount.value = Math.max(0, activePlayersCount.value - 1);
        });

        // Listen for heartbeats
        playersChannel.listenForWhisper('game.heartbeat', (data: any) => {
            console.log('Heartbeat received:', data);
        });

        // Listen for score submissions
        playersChannel.listenForWhisper('score.submit', (data: any) => {
            console.log('Score submitted by player:', data);
        });
    };

    // Listen for leaderboard updates
    const startLeaderboardListener = () => {
        leaderboardChannel = window.Echo.channel(`game.${gameSlug}.leaderboard`);
        
        leaderboardChannel.listen('ScoreSubmitted', (event: any) => {
            console.log('New score submitted:', event);
            
            // Add to recent scores
            recentScores.value.unshift({
                user_id: event.user_id,
                user_name: event.user_name,
                score: event.score,
                rank: event.rank,
                game_data: event.game_data,
                timestamp: new Date().toISOString()
            });
            
            // Keep only the 10 most recent scores
            if (recentScores.value.length > 10) {
                recentScores.value = recentScores.value.slice(0, 10);
            }
        });
    };

    // Initialize connections
    onMounted(() => {
        // Start leaderboard listener
        startLeaderboardListener();
    });

    // Cleanup on unmount
    onUnmounted(() => {
        endGameSession();
    });

    return {
        // State
        activePlayers,
        activePlayersCount,
        recentScores,
        sessionId,
        isSessionActive,
        
        // Methods
        startGameSession,
        endGameSession,
        submitScore,
        sendHeartbeat,
        startLeaderboardListener
    };
}
