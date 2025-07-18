import { Injectable } from "@angular/core";

class AudioPlaylist {
    private playlist: Array<string> = [];
    private playlistTypes: Array<string> = [];

    addTrack(audioUrl: string, audioType: string = "") {
        this.playlist.push(audioUrl);
        this.playlistTypes.push(audioType);
    }

    clear() {
        this.playlist = [];
        this.playlistTypes = [];
    }

    trackIsValid(index: number) {
        return !!this.playlist[index];
    }

    getTrackIndex(audioUrl: string) {
        return this.playlist.indexOf(audioUrl);
    }

    getTrackUrl(index: number) {
        return this.playlist[index];
    }

    getTrackType(index: number) {
        return this.playlistTypes[index];
    }

    getNumTracks() {
        return this.playlist.length;
    }
}

@Injectable()
export class AudioService {
    private audio: HTMLAudioElement = new Audio();
    private playlist: AudioPlaylist = new AudioPlaylist();
    private audioPlaybackTimer: ReturnType<typeof setTimeout> = null;
    private autoPlayIsOn: boolean = false;
    public audioType: string = "";
    private trackIndex: number = 0;

    playPauseAudio(audioUrl: string, type: string = "") {
        this.audioType = type;

        if (!audioUrl) {
            return;
        }

        // Update the audio source if necessary
        if (this.audio.src != audioUrl) {
            this.audio.src = audioUrl;
            this.audio.load();
            this.audio.play().catch((err) => {
                console.error("Error playing audio: ", err);
            });
        } else if (this.audio.paused) {
            this.audio.play().catch((err) => {
                console.error("Error playing audio: ", err);
            });
        } else {
            this.audio.pause();
        }

        // Handle autoplay for subsequent tracks in the playlist
        this.audio.onended = this.autoPlayIsOn
            ? () => this.playNextTrackInPlaylist()
            : () => {
                  this.audio.src = "";
              };

        // Clear any existing timer if not auto-playing
        if (!this.autoPlayIsOn && !!this.audioPlaybackTimer) {
            clearTimeout(this.audioPlaybackTimer);
        }
    }

    /**
     * Play the next track in the playlist if it exists
     */
    private playNextTrackInPlaylist() {
        const nextTrackIndex = this.trackIndex + 1;

        if (this.playlist.trackIsValid(nextTrackIndex)) {
            if (this.audioPlaybackTimer) {
                clearTimeout(this.audioPlaybackTimer);
            }

            this.audioPlaybackTimer = setTimeout(() => {
                this.autoPlayAudio(this.playlist.getTrackUrl(nextTrackIndex), nextTrackIndex);
            }, 1000);
        } else {
            this.pauseAndClearAudioSrc();
        }
    }

    autoPlayAudio(audioUrl: string, trackIndex: number) {
        this.trackIndex = trackIndex;
        this.playPauseAudio(audioUrl, this.playlist.getTrackType(trackIndex));
    }

    pauseAudio() {
        this.audio.pause();
    }

    getAudioSrc() {
        return this.audio.src;
    }

    audioIsPaused() {
        return this.audio.paused;
    }

    pauseAndClearAudioSrc() {
        this.setAutoPlay(false);
        this.audio.pause();
        this.audio.src = "";
        if (this.audioPlaybackTimer) {
            clearTimeout(this.audioPlaybackTimer);
        }
        this.audio.onended = null;
    }

    // Playlist
    setAutoPlay(on: boolean) {
        this.autoPlayIsOn = on;
    }

    addToPlaylist(audioUrl: string, audioType: string = "") {
        this.playlist.addTrack(audioUrl, audioType);
    }

    getPlaylistTrack(index: number) {
        return this.playlist.getTrackUrl(index);
    }

    getPlaylistTrackType(index: number) {
        return this.playlist.getTrackType(index);
    }

    clearPlaylist() {
        this.playlist.clear();
    }

    getPlaylistLength() {
        return this.playlist.getNumTracks();
    }
}
