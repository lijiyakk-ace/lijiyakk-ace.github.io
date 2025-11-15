<?php
session_start();
require 'db.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Support Center - Travel Tales</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* Base, Header, and Footer Styles from index.php */
    :root{
      --bg:#1e293b; /* slate-700 */
      --card:#0b1220;
      --muted:#9aa4b2;
      --accent:#1d9bf0;
      --glass: rgba(255,255,255,0.03);
    }
    body {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
        background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);
        color: #e6eef8;
    }
    header { display: flex; justify-content: space-between; align-items: center; padding: 20px 50px; background-color: var(--card); color: #fff; border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
    header .logo { font-size: 24px; font-weight: bold; }
    .header-right-group { display: flex; align-items: center; gap: 35px; }
    .main-nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 35px; }
    .main-nav a { color: var(--muted); text-decoration: none; font-weight: 500; font-size: 15px; padding: 5px 0; position: relative; transition: color 0.3s ease; }
    .main-nav a:hover { color: #fff; }
    .main-nav a.active { color: #fff; font-weight: 700; }
    .site-footer { background-color: var(--card); color: var(--muted); padding: 50px 50px 20px; border-top: 1px solid rgba(255,255,255,0.07); }
    .footer-main { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; }
    .footer-column h4 { color: #e6eef8; font-size: 16px; margin-bottom: 15px; font-weight: 600; }
    .footer-column ul { list-style: none; padding: 0; margin: 0; }
    .footer-column ul li { margin-bottom: 10px; }
    .footer-column ul a { color: var(--muted); text-decoration: none; font-size: 14px; transition: color 0.3s ease; }
    .footer-column ul a:hover { color: var(--accent); }
    .footer-bottom { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding-top: 20px; border-top: 1px solid var(--glass); font-size: 13px; }
    .social-links, .legal-links { display: flex; gap: 15px; }

    /* Support Page Specific Styles */
    .wrap{max-width:none;margin:48px 50px;padding:0;}
    .page-header{text-align:center; margin-bottom: 30px;}
    .page-header h1{font-size:36px;margin-bottom:10px; color: #fff;}
    .page-header p{color:var(--muted);font-size:18px; max-width: 700px; margin: 0 auto;}

    .grid{display:grid;grid-template-columns:1fr 360px;gap:24px}
    .support-card{background:var(--card);border-radius:14px;padding:28px;box-shadow:0 6px 22px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.07);}

    /* left column */
    .support-hero{display:flex;flex-direction:column;gap:20px}
    .support-hero .title{font-size:20px;font-weight:600; color: #fff;}
    .support-hero .desc{color:var(--muted);font-size:15px; line-height: 1.6;}

    .contact-options{display:flex;gap:16px;margin-top:10px}
    .contact-card{flex:1;padding:20px;border-radius:12px;background:var(--glass);border:1px solid rgba(255,255,255,0.05)}
    .contact-card h4{margin-bottom:8px;font-size:16px; color: #fff;}
    .contact-card p{font-size:14px;color:var(--muted); line-height: 1.6;}
    .btn{display:inline-block;padding:10px 18px;border-radius:8px;font-weight:700;background:var(--accent);color:#0b1220;text-decoration:none; transition: transform 0.2s;}
    .btn:hover { transform: scale(1.05); }

    /* form */
    form.support-form{margin-top:18px;display:grid;gap:16px}
    .field{display:flex;flex-direction:column}
    label{font-size:14px;color:#e6eef8;margin-bottom:8px; font-weight: 500;}
    input[type="text"], input[type="email"], textarea, select{
        padding:12px;
        border-radius:8px;
        border:1px solid rgba(255,255,255,0.1);
        background:var(--glass);
        color: #e6eef8;
        font-size:15px;
        font-family: 'Montserrat', sans-serif;
    }
    textarea{min-height:140px;resize:vertical}
    .row{display:flex;gap:16px}
    .row .field{flex:1}

    /* right column */
    .help-panel{display:flex;flex-direction:column;gap:16px}
    .panel-hero{padding:24px;border-radius:12px;background:linear-gradient(135deg, var(--accent), #3bb0ff);color:#0b1220;}
    .panel-hero h3{margin-bottom:8px; color: #fff;}
    .panel-hero p{opacity:0.9; color: #0b1220; font-weight: 500;}

    .faq{margin-top:8px}
    .faq-item{border-radius:10px;padding:15px;border:1px solid rgba(255,255,255,0.05);background:var(--glass)}
    .faq-item + .faq-item{margin-top:12px}
    .q{display:flex;justify-content:space-between;align-items:center;cursor:pointer; font-weight: 600; color: #fff;}
    .a{margin-top:12px;color:var(--muted);font-size:14px;display:none; line-height: 1.7;}

    /* responsive */
    .faq-section {
        background: var(--card); border-radius: 14px; padding: 28px;
        box-shadow: 0 6px 22px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.07);
        margin-top: 24px;
    }
    @media (max-width:900px){
      .grid{grid-template-columns:1fr;}
      .wrap{padding:28px;margin:28px auto}
    }
  </style>
</head>
<body>

<?php require 'header.php'; ?>

  <div class="wrap">
    <header class="page-header">
      <h1>Support Center</h1>
      <p>We're here to help — reach out with any issue and our support team will reply within 24 hours.</p>
    </header>

    <main class="grid">
      <!-- LEFT: main support content -->
      <section class="support-card support-hero">
        <div>
          <div class="title">How can we help you today?</div>
          <div class="desc">Choose an option below or use the contact form to submit your request. For urgent matters, please call our hotline listed in the panel.</div>
        </div>

        <div class="contact-options">
          <div class="contact-card">
            <h4>Email Support</h4>
            <p>Send us an email and we'll get back within one business day.</p>
            <div style="margin-top:10px"><a class="btn" href="mailto:support@traveltales.com">Email Us</a></div>
          </div>

          <div class="contact-card">
            <h4>WhatsApp Chat</h4>
            <p>Get instant help by chatting with us directly on WhatsApp.</p>
            <div style="margin-top:10px">
              <a href="https://wa.me/919876543210?text=Hi%20there!%20I%20need%20support%20regarding%20my%20account." 
                 target="_blank"
                 class="btn" style="background:#25D366;color:white;">
                 💬 Chat on WhatsApp
              </a>
            </div>
          </div>
        </div>

        <form class="support-form" id="supportForm" onsubmit="return handleSubmit(event)">
          <div class="row">
            <div class="field">
              <label for="name">Full name</label>
              <input id="name" name="name" type="text" placeholder="Your full name" required />
            </div>
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="you@domain.com" required />
            </div>
          </div>

          <div class="field">
            <label for="topic">Topic</label>
            <select id="topic" name="topic">
              <option>General question</option>
              <option>Billing</option>
              <option>Technical issue</option>
              <option>Feature request</option>
            </select>
          </div>

          <div class="field">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Describe your issue in detail" required></textarea>
          </div>

          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px">
            <div style="color:var(--muted);font-size:13px">Attach screenshot (optional)</div>
            <button class="btn" type="submit">Send request</button>
          </div>
        </form>

        <div style="margin-top:14px;color:var(--muted);font-size:13px">You can also check our <a href="#faq" style="text-decoration: underline;">FAQ</a> for quick answers.</div>
      </section>

      <!-- RIGHT: help panel -->
      <aside class="support-card help-panel">
        <div class="panel-hero">
          <h3>Need faster help?</h3>
          <p>Call our support hotline: <strong>+1 (555) 987-6543</strong><br/>Available 24/7 for urgent issues.</p>
        </div>

        <div class="faq" id="faq">
          <div class="faq-item">
            <div class="q" onclick="toggleFaq(this)">
              <div>How do I change my password?</div>
              <div>+</div>
            </div>
            <div class="a">Go to your account settings &raquo; Security &raquo; Change password. If you cannot log in, use 'Forgot password' on the sign-in page.</div>
          </div>

          <div class="faq-item">
            <div class="q" onclick="toggleFaq(this)">
              <div>Where can I view my invoices?</div>
              <div>+</div>
            </div>
            <div class="a">Invoices are available under Account &raquo; Billing. You can download PDF copies or request them via email.</div>
          </div>

          <div class="faq-item">
            <div class="q" onclick="toggleFaq(this)">
              <div>How long does a refund take?</div>
              <div>+</div>
            </div>
            <div class="a">Refunds are processed within 5–7 business days after approval. The time it appears in your account depends on your bank.</div>
          </div>
        </div>

        <div style="margin-top:auto;padding-top:8px;border-top:1px dashed rgba(255,255,255,0.05);">
          <div style="font-size:13px;color:var(--muted)">Support hours: Mon–Fri 9:00–18:00</div>
          <div style="font-size:13px;color:var(--muted)">Email: <a href="mailto:support@traveltales.com" style="color: var(--muted);">support@traveltales.com</a></div>
        </div>
      </aside>
    </main>

<section class="faq-section" id="more-faq">
  <h2 style="margin-bottom:16px;font-size:20px;font-weight:600;">Frequently Asked Questions</h2>

  <div class="faq-list">
    <div class="faq-item">
      <div class="q" onclick="toggleFaq(this)">
        <div>How can I reset my password?</div>
        <div>+</div>
      </div>
      <div class="a">
        Go to the login page and click <b>“Forgot Password”</b>. Follow the instructions sent to your email to reset it safely.
      </div>
    </div>

    <div class="faq-item">
      <div class="q" onclick="toggleFaq(this)">
        <div>How do I update my account information?</div>
        <div>+</div>
      </div>
      <div class="a">
        You can update your profile by logging into your account and navigating to <b>Settings → Profile</b>. Make sure to save changes before leaving the page.
      </div>
    </div>

    <div class="faq-item">
      <div class="q" onclick="toggleFaq(this)">
        <div>How do I report a technical issue?</div>
        <div>+</div>
      </div>
      <div class="a">
        Use the contact form on this page or reach out directly via our WhatsApp chat. Include a detailed description or screenshot of the issue.
      </div>
    </div>

    <div class="faq-item">
      <div class="q" onclick="toggleFaq(this)">
        <div>Is my personal information secure?</div>
        <div>+</div>
      </div>
      <div class="a">
        Yes. We use secure connections and store your information safely. Your privacy is always protected.
      </div>
    </div>

    <div class="faq-item">
      <div class="q" onclick="toggleFaq(this)">
        <div>How can I contact the support team directly?</div>
        <div>+</div>
      </div>
      <div class="a">
        You can contact us via the WhatsApp chat button on this page or by sending an email to <b>support@example.com</b>. We respond within 24 hours.
      </div>
    </div>
  </div>
</section>

  </div>

<?php require 'footer.php'; ?>

  <script>
    function toggleFaq(el){
      const a = el.parentElement.querySelector('.a');
      const sign = el.querySelector(':last-child');
      const visible = a.style.display === 'block';
      a.style.display = visible ? 'none' : 'block';
      el.querySelector(':last-child').textContent = visible ? '+' : '−';
    }

    function handleSubmit(e){
      e.preventDefault();
      const btn = e.target.querySelector('button[type=submit]');
      btn.textContent = 'Sending...';
      btn.disabled = true;

      // Simulate async submission — replace with real endpoint
      setTimeout(()=>{
        alert('Support request submitted. We\'ll contact you at ' + document.getElementById('email').value);
        e.target.reset();
        btn.textContent = 'Send request';
        btn.disabled = false;
      },900);

      return false;
    }
  </script>
</body>
</html>