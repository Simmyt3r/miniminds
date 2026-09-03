/**
 * Gentle, asset-free sound cues for MiniMinds.
 * Sounds are off until a child or grown-up turns them on, and every cue is
 * generated locally with Web Audio—no tracking, downloads, or surprise audio.
 */
(function () {
  const KEYS = { enabled: 'miniminds-sounds-enabled', volume: 'miniminds-sounds-volume' };
  const toggle = document.querySelector('[data-sound-toggle]');
  const slider = document.querySelector('[data-sound-volume]');
  const status = document.querySelector('[data-sound-status]');
  let enabled = localStorage.getItem(KEYS.enabled) === 'true';
  let volume = Number(localStorage.getItem(KEYS.volume) || 65);
  let context;
  let messageTimer;

  const updateControls = () => {
    toggle?.setAttribute('aria-pressed', String(enabled));
    toggle?.setAttribute('aria-label', enabled ? 'Turn off happy sounds' : 'Turn on happy sounds');
    const icon = toggle?.querySelector('[aria-hidden="true"]');
    const label = toggle?.querySelector('.sound-toggle-label');
    if (icon) icon.textContent = enabled ? '🔊' : '🔇';
    if (label) label.textContent = enabled ? 'Sounds on' : 'Sounds off';
    if (slider) slider.value = String(volume);
  };

  const announce = (text) => {
    if (!status) return;
    status.textContent = text;
    status.classList.add('show');
    clearTimeout(messageTimer);
    messageTimer = setTimeout(() => status.classList.remove('show'), 1800);
  };

  const audio = () => {
    if (!context) context = new (window.AudioContext || window.webkitAudioContext)();
    if (context.state === 'suspended') context.resume();
    return context;
  };

  const tone = (frequency, start, duration, type = 'sine') => {
    if (!enabled || volume === 0 || !window.AudioContext && !window.webkitAudioContext) return;
    const ctx = audio();
    const oscillator = ctx.createOscillator();
    const gain = ctx.createGain();
    const begins = ctx.currentTime + start;
    const loudness = (volume / 100) * 0.09;
    oscillator.type = type;
    oscillator.frequency.setValueAtTime(frequency, begins);
    gain.gain.setValueAtTime(0.0001, begins);
    gain.gain.exponentialRampToValueAtTime(loudness, begins + 0.015);
    gain.gain.exponentialRampToValueAtTime(0.0001, begins + duration);
    oscillator.connect(gain).connect(ctx.destination);
    oscillator.start(begins);
    oscillator.stop(begins + duration + 0.02);
  };

  const patterns = {
    tap: () => tone(440, 0, 0.08, 'sine'),
    sparkle: () => { tone(660, 0, 0.1); tone(880, 0.09, 0.14); },
    correct: () => { tone(523, 0, 0.1); tone(659, 0.1, 0.11); tone(784, 0.21, 0.16); },
    tryAgain: () => { tone(294, 0, 0.12, 'triangle'); tone(330, 0.13, 0.12, 'triangle'); },
    celebrate: () => { tone(523, 0, 0.12); tone(659, 0.12, 0.12); tone(784, 0.24, 0.15); tone(1047, 0.38, 0.22); }
  };

  const play = (name) => (patterns[name] || patterns.tap)();
  window.MiniMindsSound = { play };
  document.addEventListener('miniminds:sound', (event) => play(event.detail?.name));
  document.addEventListener('click', (event) => {
    if (event.target.closest('[data-sound-toggle], [data-sound-volume]')) return;
    const target = event.target.closest('[data-sound]');
    if (target) play(target.dataset.sound);
    else if (event.target.closest('button, .button')) play('tap');
  });
  toggle?.addEventListener('click', () => {
    enabled = !enabled;
    localStorage.setItem(KEYS.enabled, String(enabled));
    updateControls();
    if (enabled) { play('sparkle'); announce('Happy sounds are on!'); }
    else announce('Sounds are off.');
  });
  slider?.addEventListener('input', () => {
    volume = Number(slider.value);
    localStorage.setItem(KEYS.volume, String(volume));
    if (enabled) play('tap');
  });
  updateControls();
}());
