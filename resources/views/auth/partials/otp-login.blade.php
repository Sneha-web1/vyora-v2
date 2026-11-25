{{--
    Inline "Login with Email OTP". Included INSIDE the login form (no popup).
    Needs: $otpContext (superadmin|role|employee), $otpRoleSlug (role panels only),
           $otpFieldName (name of the login-id input on the page: "email" or "patId")

    Screens:  normal login  ->  [click] ->  (email entry, only if the email box was empty)  ->  Enter OTP screen
    On the OTP screens the email / password / remember / sign-in blocks are hidden.
--}}
<div id="otpWrap">
    <div id="otpMsg" class="small mb-3 d-none"></div>

    {{-- entry point shown on the normal login screen --}}
    <div id="otpStartBox" class="mb-3">
        <div class="text-center text-muted small mb-2">or</div>
        <button type="button" id="otpStartBtn" class="btn btn-outline-primary w-100">
            <i class="ti ti-mail me-1"></i>Login with Email OTP
        </button>
    </div>

    {{-- step 1: only when no valid email was typed (email box stays visible above) --}}
    <div id="otpStep1" class="d-none">
        <button type="button" id="otpSendBtn" class="btn btn-primary w-100 mb-3">Send OTP</button>
        <div class="text-center small mb-3">
            <a href="javascript:void(0);" class="otpBack">Back to Sign In</a>
        </div>
    </div>

    {{-- step 2: Enter OTP screen --}}
    <div id="otpStep2" class="d-none">
        <div class="mb-3">
            <label class="form-label">One-Time Password</label>
            <input type="text" id="otpCode" class="form-control text-center" inputmode="numeric" maxlength="6"
                autocomplete="one-time-code" placeholder="------" style="letter-spacing:10px;font-size:1.4rem;">
        </div>
        <button type="button" id="otpVerifyBtn" class="btn btn-primary w-100 mb-3">Verify &amp; Login</button>
        <div class="d-flex justify-content-between small mb-3">
            <a href="javascript:void(0);" id="otpResend" class="text-muted">Resend OTP</a>
            <a href="javascript:void(0);" class="otpBack">Back to Sign In</a>
        </div>
    </div>
</div>

<script>
    (function () {
        var ctx = @json($otpContext), slug = @json($otpRoleSlug ?? null), fieldName = @json($otpFieldName),
            sendUrl = @json(route('login.otp.send')), verifyUrl = @json(route('login.otp.verify')),
            csrf = @json(csrf_token());
        var $ = function (id) { return document.getElementById(id); };
        var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        var wrap = $('otpWrap'), form = wrap.closest('form'), container = wrap.parentElement;
        var idField = document.querySelector('input[name="' + fieldName + '"]');
        var h2 = form.querySelector('h2'), sub = h2 ? h2.nextElementSibling : null;
        var h2Text = h2 ? h2.textContent : '', subText = sub ? sub.textContent : '';
        var otpEmail = '', timer = null, hidden = [];

        function setHeading(t, s) { if (h2) h2.textContent = t; if (sub) sub.textContent = s; }

        function msg(text, ok) {
            var m = $('otpMsg');
            m.textContent = text;
            m.className = 'small mb-3 text-center ' + (ok ? 'text-success' : 'text-danger');
        }
        function clearMsg() { $('otpMsg').className = 'd-none'; $('otpMsg').textContent = ''; }

        // hide everything in the form except heading, the OTP block (and optionally the email box)
        function hideOthers(keepEmail) {
            showOthers();
            Array.prototype.forEach.call(container.children, function (el) {
                if (el === wrap || el.contains(wrap) || (h2 && el.contains(h2)) || (keepEmail && idField && el.contains(idField))) return;
                el.classList.add('d-none');
                hidden.push(el);
            });
        }
        function showOthers() { hidden.forEach(function (el) { el.classList.remove('d-none'); }); hidden = []; }

        function show(stepId) {
            ['otpStartBox', 'otpStep1', 'otpStep2'].forEach(function (id) {
                $(id).classList.toggle('d-none', id !== stepId);
            });
        }

        function toEmailStep() {
            hideOthers(true);
            show('otpStep1');
            setHeading('Login with Email OTP', 'Enter your registered email to receive a one-time password');
            if (idField) idField.focus();
        }

        function toOtpStep() {
            hideOthers(false);
            show('otpStep2');
            var at = otpEmail.indexOf('@');
            setHeading('Enter OTP', 'We sent a 6-digit code to ' + otpEmail.charAt(0) + '****' + otpEmail.slice(at));
            $('otpCode').value = '';
            $('otpCode').focus();
        }

        function toNormal() {
            clearInterval(timer);
            showOthers();
            show('otpStartBox');
            setHeading(h2Text, subText);
            $('otpCode').value = '';
            clearMsg();
        }

        function post(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify(Object.assign({context: ctx, role_slug: slug, identifier: otpEmail}, body || {}))
            }).then(function (r) { return r.json().then(function (j) { j._status = r.status; return j; }); });
        }

        function startCooldown() {
            var left = 60, a = $('otpResend');
            a.style.pointerEvents = 'none';
            a.textContent = 'Resend OTP in 60s';
            clearInterval(timer);
            timer = setInterval(function () {
                left--;
                a.textContent = left > 0 ? 'Resend OTP in ' + left + 's' : 'Resend OTP';
                if (left <= 0) { clearInterval(timer); a.style.pointerEvents = ''; }
            }, 1000);
        }

        function sendOtp() {
            if (!emailRe.test(otpEmail)) { msg('Please enter a valid email address.', false); return; }
            var btns = [$('otpStartBtn'), $('otpSendBtn')];
            $('otpResend').style.pointerEvents = 'none';
            btns.forEach(function (b) { b.disabled = true; });
            post(sendUrl).then(function (j) {
                btns.forEach(function (b) { b.disabled = false; });
                if (j._status === 429) { $('otpResend').style.pointerEvents = ''; msg('Too many requests. Please wait a minute.', false); return; }
                if (j._status === 422) { $('otpResend').style.pointerEvents = ''; msg('Please enter a valid email address.', false); return; }
                clearMsg();
                startCooldown();
            }).catch(function () {
                btns.forEach(function (b) { b.disabled = false; });
                $('otpResend').style.pointerEvents = '';
                msg('Something went wrong. Try again.', false);
            });
        }

        function verifyOtp() {
            var code = $('otpCode').value.trim();
            if (!/^\d{6}$/.test(code)) { msg('Enter the 6-digit OTP.', false); return; }
            var b = $('otpVerifyBtn'); b.disabled = true;
            post(verifyUrl, {otp: code}).then(function (j) {
                if (j.ok) { msg('Login successful. Redirecting...', true); window.location = j.redirect; return; }
                b.disabled = false;
                msg(j._status === 429 ? 'Too many attempts. Please wait a minute.' : (j.message || 'Invalid or expired OTP.'), false);
            }).catch(function () { b.disabled = false; msg('Something went wrong. Try again.', false); });
        }

        // "Login with Email OTP": a valid email already typed -> send right away, else ask for it first
        $('otpStartBtn').addEventListener('click', function () {
            clearMsg();
            var v = idField ? idField.value.trim() : '';
            // go to the Enter OTP screen instantly; the OTP is sent in the background
            if (emailRe.test(v)) { otpEmail = v; toOtpStep(); sendOtp(); } else { toEmailStep(); }
        });
        $('otpSendBtn').addEventListener('click', function () {
            var v = idField ? idField.value.trim() : '';
            if (!emailRe.test(v)) { msg('Please enter a valid email address.', false); return; }
            otpEmail = v; toOtpStep(); sendOtp();
        });
        $('otpResend').addEventListener('click', sendOtp);
        $('otpVerifyBtn').addEventListener('click', verifyOtp);
        Array.prototype.forEach.call(document.querySelectorAll('.otpBack'), function (a) { a.addEventListener('click', toNormal); });
        $('otpCode').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); verifyOtp(); } });
        $('otpCode').addEventListener('input', function () { this.value = this.value.replace(/\D/g, ''); });
        // Enter in the email box during the email step must not submit the password form
        if (idField) idField.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !$('otpStep1').classList.contains('d-none')) { e.preventDefault(); $('otpSendBtn').click(); }
        });
    })();
</script>
