(function () {
  const playground = document.querySelector('[data-lesson-playground]');
  if (!playground) return;

  const data = playground.dataset;
  const complete = () => {
    playground.querySelector('.completion-area').hidden = false;
    playground.querySelector('.completion-area').scrollIntoView({ behavior: 'smooth', block: 'center' });
  };
  const celebrate = (message) => {
    const note = document.createElement('p');
    note.className = 'play-feedback success';
    note.textContent = `✨ ${message}`;
    playground.querySelector('.play-feedback')?.remove();
    playground.querySelector('.game-stage, .story-stage').append(note);
  };

  if (data.lessonType === 'story') {
    const scenes = [
      {
        text: `${data.character} arrives in ${data.setting} with a puzzle about ${data.topic}. A little sign points in two directions.`,
        choices: ['Look closely for a clue 🔎', 'Ask a friend for an idea 💬']
      },
      {
        text: `A friendly helper smiles. “Big problems become easier when we try one small step at a time!” ${data.character} has a new plan.`,
        choices: ['Try the first small step 👣', 'Draw the plan first ✏️']
      },
      {
        text: `The plan works! ${data.character} discovers that ${data.topic} is all about noticing, trying, and learning. The whole of ${data.setting} cheers!`,
        choices: ['Celebrate the discovery 🎉']
      }
    ];
    let scene = 0;
    const stage = playground.querySelector('.story-stage');
    const renderStory = () => {
      const current = scenes[scene];
      stage.innerHTML = `<div class="story-progress" aria-label="Story progress">${scenes.map((_, i) => `<span class="${i <= scene ? 'filled' : ''}"></span>`).join('')}</div><div class="story-character">${scene === 2 ? '🏆' : '🧭'}</div><p class="story-copy">${current.text}</p><div class="choice-grid">${current.choices.map((choice, i) => `<button type="button" class="choice-button" data-choice="${i}">${choice}</button>`).join('')}</div>`;
      stage.querySelectorAll('.choice-button').forEach(button => button.addEventListener('click', () => {
        if (scene === scenes.length - 1) {
          stage.querySelector('.choice-grid').hidden = true;
          celebrate(`You guided ${data.character} through the story!`);
          complete();
        } else {
          scene += 1;
          renderStory();
        }
      }));
    };
    renderStory();
    return;
  }

  const challenges = [
    {
      question: `Which choice helps ${data.character} get started with ${data.topic}?`,
      answers: ['Try one clear step at a time', 'Do nothing and hope', 'Choose every answer at once'], correct: 0
    },
    {
      question: `A plan has three steps. What should happen first?`,
      answers: ['Start with step 1', 'Jump to the last step', 'Mix up the steps'], correct: 0
    },
    {
      question: `What is a great thing to do when something does not work yet?`,
      answers: ['Try, notice, and make a small change', 'Give up immediately', 'Hide the challenge'], correct: 0
    }
  ];
  const stage = playground.querySelector('.game-stage');
  let round = 0;
  const renderGame = () => {
    const challenge = challenges[round];
    stage.innerHTML = `<div class="game-status"><span>Challenge ${round + 1} of ${challenges.length}</span><span class="star-bank">${'⭐'.repeat(round)}${'☆'.repeat(challenges.length - round)}</span></div><div class="mission-orb">${round === 0 ? '🚀' : round === 1 ? '🧩' : '🌈'}</div><h3>${challenge.question}</h3><div class="answer-list">${challenge.answers.map((answer, index) => `<button type="button" class="answer-button" data-answer="${index}">${answer}</button>`).join('')}</div>`;
    stage.querySelectorAll('.answer-button').forEach(button => button.addEventListener('click', () => {
      const selected = Number(button.dataset.answer);
      if (selected !== challenge.correct) {
        button.classList.add('try-again');
        button.textContent = 'Almost! Try the thoughtful choice 💛';
        return;
      }
      stage.querySelectorAll('.answer-button').forEach(answer => answer.disabled = true);
      button.classList.add('correct');
      button.textContent = 'Great thinking! ⭐';
      setTimeout(() => {
        round += 1;
        if (round === challenges.length) {
          stage.innerHTML = `<div class="win-card"><div>🏆</div><h3>You collected all the stars!</h3><p>${data.character} is proud of your ${data.topic} skills.</p></div>`;
          complete();
        } else renderGame();
      }, 650);
    }));
  };
  renderGame();
}());
