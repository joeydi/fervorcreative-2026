/**
 * Playable videos: a play button over a still (an image or a looping muted
 * video) that holds the space until it's pressed, then plays the full video
 * with sound in its place under our own controls.
 *
 * Markup:
 *   [data-player]               – the frame, with data-player-src (a video
 *                                 file) or data-player-vimeo (a Vimeo URL)
 *     [data-player-stage]       – where the full video goes, over the still
 *     [data-player-controls]
 *       [data-player-toggle]    – play / pause button, with aria-label and
 *                                 data-label-pause for its two states
 *       [data-player-progress]  – input[type=range] from 0 to 1, for seeking
 *       [data-player-mute]      – mute toggle (aria-pressed)
 *
 * The frame gets .is-started once the full video first plays (until it ends),
 * .is-playing while it plays and .is-idle once nothing has been touched for a
 * while during playback, which the styles use to fade the controls out. The
 * progress bar fills from --progress (0 to 1) on the range.
 *
 * Vimeo videos need the owner's account to allow hiding Vimeo's own controls.
 */

// Controls fade out after this long without a touch while playing (ms).
const IDLE = 3000;

// Only one video plays at a time.
let current = null;

// A video file in a <video>.
async function fileMedia(stage, src, on) {
	const video = document.createElement('video');
	video.className = 'project-media__full';
	video.src = src;
	video.playsInline = true;
	video.preload = 'auto';
	stage.append(video);

	video.addEventListener('playing', on.play);
	video.addEventListener('pause', () => !video.ended && on.pause());
	video.addEventListener('ended', on.ended);
	video.addEventListener('volumechange', () => on.muted(video.muted));

	return {
		play: () => video.play(),
		pause: () => video.pause(),
		setMuted: (muted) => (video.muted = muted),
		seek: (fraction) => video.duration && (video.currentTime = fraction * video.duration),
		progress: () => (video.duration ? video.currentTime / video.duration : 0),
	};
}

// A Vimeo video in an iframe, driven through its player API.
async function vimeoMedia(stage, url, on) {
	const { default: Player } = await import('@vimeo/player');
	const player = new Player(stage, { url, controls: false, dnt: true, playsinline: true, title: false, byline: false, portrait: false });

	// Vimeo reports the time a few times a second; between reports it's
	// carried on from the last one so the bar moves smoothly.
	let duration = 0;
	let seconds = 0;
	let at = 0;
	let playing = false;

	const sync = (data) => {
		duration = data.duration;
		seconds = data.seconds;
		at = performance.now();
	};

	player.on('timeupdate', sync);
	player.on('seeked', sync);
	player.on('play', (data) => {
		playing = true;
		sync(data);
		on.play();
	});
	player.on('pause', (data) => {
		playing = false;
		sync(data);
		on.pause();
	});
	player.on('ended', () => {
		playing = false;
		on.ended();
	});
	player.on('volumechange', () => player.getMuted().then(on.muted));

	await player.ready();
	duration = await player.getDuration();

	return {
		play: () => player.play(),
		pause: () => player.pause(),
		setMuted: (muted) => player.setMuted(muted).then(() => on.muted(muted)),
		seek: (fraction) => {
			seconds = fraction * duration;
			at = performance.now();
			return player.setCurrentTime(seconds);
		},
		progress: () => {
			if (!duration) return 0;
			const elapsed = playing ? (performance.now() - at) / 1000 : 0;
			return Math.min((seconds + elapsed) / duration, 1);
		},
	};
}

function initPlayer(root) {
	const stage = root.querySelector('[data-player-stage]');
	const toggle = root.querySelector('[data-player-toggle]');
	const progress = root.querySelector('[data-player-progress]');
	const mute = root.querySelector('[data-player-mute]');
	const still = root.querySelector('video');

	let media = null;
	let loading = null;
	let playing = false;
	let seeking = false;
	let idleTimer = 0;
	let frame = 0;
	let wasIdle = false;
	let pointerType = '';

	const setProgress = (fraction) => {
		progress.value = fraction;
		progress.style.setProperty('--progress', fraction);
	};

	const tick = () => {
		if (!seeking) setProgress(media.progress());
		frame = playing ? requestAnimationFrame(tick) : 0;
	};

	// Shows the controls, and hides them again after a while if still playing.
	const wake = () => {
		root.classList.remove('is-idle');
		clearTimeout(idleTimer);
		if (playing) {
			idleTimer = setTimeout(() => root.classList.add('is-idle'), IDLE);
		}
	};

	const setPlaying = (state) => {
		playing = state;
		root.classList.toggle('is-playing', state);
		toggle.setAttribute('aria-label', state ? toggle.dataset.labelPause : toggle.dataset.labelPlay);
		cancelAnimationFrame(frame);
		if (state) tick();
		wake();
	};

	const on = {
		play() {
			if (current && current !== api) current.pause();
			current = api;
			root.classList.add('is-started');
			root.removeAttribute('aria-busy');
			still?.pause();
			setPlaying(true);
		},
		pause() {
			setPlaying(false);
			tick();
		},
		// Back to the still, ready to play again from the start.
		ended() {
			setPlaying(false);
			root.classList.remove('is-started');
			setProgress(0);
			media.seek(0);
			still?.play();
		},
		muted(state) {
			mute.setAttribute('aria-pressed', String(state));
		},
	};

	const load = () => {
		root.setAttribute('aria-busy', 'true');
		const make = root.dataset.playerVimeo ? vimeoMedia(stage, root.dataset.playerVimeo, on) : fileMedia(stage, root.dataset.playerSrc, on);
		return make.then((made) => (media = made));
	};

	// Starting with sound needs the press that started it; if the browser
	// won't allow it by the time the video's ready, play muted instead.
	const play = async () => {
		loading ||= load();
		await loading;
		try {
			await media.play();
		} catch (error) {
			if (error?.name !== 'NotAllowedError') throw error;
			await media.setMuted(true);
			on.muted(true);
			await media.play();
		}
	};

	const api = { pause: () => media?.pause() };

	toggle.dataset.labelPlay = toggle.getAttribute('aria-label');

	toggle.addEventListener('click', () => (playing ? media.pause() : play()));
	mute.addEventListener('click', () => media?.setMuted(mute.getAttribute('aria-pressed') !== 'true'));

	progress.addEventListener('input', () => {
		seeking = true;
		setProgress(Number(progress.value));
		media?.seek(Number(progress.value));
	});
	progress.addEventListener('change', () => (seeking = false));

	// Pressing the picture plays or pauses, except that a tap on hidden
	// controls only brings them back.
	root.addEventListener('pointerdown', (event) => {
		wasIdle = root.classList.contains('is-idle');
		pointerType = event.pointerType;
	});
	root.addEventListener('click', (event) => {
		if (event.target.closest('[data-player-controls]') || (wasIdle && pointerType !== 'mouse')) return;
		playing ? media.pause() : play();
	});

	['pointermove', 'pointerdown', 'keydown', 'focusin'].forEach((type) => root.addEventListener(type, wake));
}

export default function player() {
	document.querySelectorAll('[data-player]').forEach(initPlayer);
}
