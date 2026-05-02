
    const inputs = Array.from(document.querySelectorAll('.otp-digit'));
    const verifyBtn = document.getElementById('verifyBtn');
    const resendBtn = document.getElementById('resendBtn');
    const countdownEl = document.getElementById('countdown');
    const otpHidden = document.getElementById('otpHidden');

    let timer;
    const COUNTDOWN_SECONDS = 600; // 10 minutes
    const STORAGE_KEY = 'otp_countdown_end';

    if (inputs.length > 0) {
        inputs[0].focus();
    }

    inputs.forEach((inp, i) => {
        inp.addEventListener('input', () => {
            let val = inp.value.replace(/\D/g, '').slice(-1);
            inp.value = val;

            if (val) {
                inp.classList.add('filled');
                if (i < inputs.length - 1) inputs[i + 1].focus();
            } else {
                inp.classList.remove('filled');
            }

            updateDots();
            checkComplete();
        });

        inp.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                if (!inp.value && i > 0) {
                    inputs[i - 1].value = '';
                    inputs[i - 1].classList.remove('filled');
                    inputs[i - 1].focus();
                    updateDots();
                    checkComplete();
                }
            } else if (!/^\d$/.test(e.key) && !['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'].includes(e.key)) {
                e.preventDefault();
            }
        });

        inp.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, inputs.length - i);

            pasted.split('').forEach((ch, j) => {
                if (inputs[i + j]) {
                    inputs[i + j].value = ch;
                    inputs[i + j].classList.add('filled');
                }
            });

            updateDots();
            checkComplete();

            const nextEmpty = inputs.find(input => !input.value);
            if (nextEmpty) nextEmpty.focus();
            else inputs[inputs.length - 1].focus();
        });
    });

    function updateDots() {
        inputs.forEach((inp, i) => {
            const dot = document.getElementById('pd' + i);
            if (dot) {
                dot.classList.toggle('active', !!inp.value);
            }
        });
    }

    function checkComplete() {
        const otp = inputs.map(inp => inp.value).join('');
        otpHidden.value = otp;

        const allFilled = inputs.every(inp => inp.value.length === 1);
        verifyBtn.disabled = !allFilled;
    }

    function handleVerify() {
        verifyBtn.textContent = '⏳ Verifying...';
        verifyBtn.disabled = true;

        setTimeout(() => {
            document.getElementById('cardMain').classList.add('hide');
            document.getElementById('successOverlay').classList.add('show');
        }, 1200);
    }

    function updateCountdownDisplay(timeLeft) {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        countdownEl.textContent = `(${minutes}:${String(seconds).padStart(2, '0')})`;
    }

    function getTimeLeft() {
        const endTime = localStorage.getItem(STORAGE_KEY);
        if (!endTime) return 0;

        const diff = Math.floor((parseInt(endTime, 10) - Date.now()) / 1000);
        return diff > 0 ? diff : 0;
    }

    function startCountdown(duration = COUNTDOWN_SECONDS) {
        const endTime = Date.now() + duration * 1000;
        localStorage.setItem(STORAGE_KEY, endTime);

        runCountdown();
    }

    function runCountdown() {
        clearInterval(timer);

        let timeLeft = getTimeLeft();

        if (timeLeft <= 0) {
            resendBtn.disabled = false;
            countdownEl.style.display = 'none';
            localStorage.removeItem(STORAGE_KEY);
            return;
        }

        resendBtn.disabled = true;
        countdownEl.style.display = 'inline';
        updateCountdownDisplay(timeLeft);

        timer = setInterval(() => {
            timeLeft = getTimeLeft();
            updateCountdownDisplay(timeLeft);

            if (timeLeft <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
                countdownEl.style.display = 'none';
                localStorage.removeItem(STORAGE_KEY);
            }
        }, 1000);
    }

    function startResend() {
        inputs.forEach(inp => {
            inp.value = '';
            inp.classList.remove('filled');
        });

        otpHidden.value = '';
        updateDots();
        checkComplete();

        if (inputs.length > 0) {
            inputs[0].focus();
        }

        startCountdown(COUNTDOWN_SECONDS);
    }

    // resume countdown after refresh
    if (localStorage.getItem(STORAGE_KEY)) {
        runCountdown();
    } else {
        startCountdown(COUNTDOWN_SECONDS);
    }