<?php
// Start session for login check
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Create Quiz</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root{--bg:#071229;--card:#081428;--muted:#97a6bd;--accent:#1d9bf0;--glass:rgba(255,255,255,0.03);--radius:12px}
    *{box-sizing:border-box}
    body{margin:0;font-family:'Montserrat',sans-serif;color:#e6eef8;background:linear-gradient(180deg,#061020 0%,#071229 100%);padding:0;}
    a{color:inherit}
    .container{max-width:1100px;margin:0 auto; padding: 28px;}

    /* Header */
    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 50px;
        background-color: var(--card);
        color: #fff;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    header .logo {
        font-size: 24px;
        font-weight: bold;
    }

    /* Header Right Group for Nav and Auth */
    .header-right-group {
        display: flex;
        align-items: center;
        gap: 35px; /* Space between nav links and auth buttons */
    }

    /* Main Navigation */
    .main-nav {
        display: flex;
        justify-content: center;
    }
    .main-nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        gap: 35px;
    }
    .main-nav a {
        color: var(--muted);
        text-decoration: none;
        font-weight: 500;
        font-size: 15px;
        padding: 5px 0;
        position: relative;
        transition: color 0.3s ease;
    }
    .main-nav a:hover {
        color: #fff;
    }
    .main-nav a.active {
        color: #fff;
        font-weight: 700;
    }
    .main-nav a.active::after {
        content: '';
        position: absolute;
        bottom: -20px; /* Aligns with the header's bottom border */
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--accent);
    }

    .chips{display:flex;gap:8px}
    .chip{background:var(--glass);padding:6px 10px;border-radius:999px;font-size:13px;color:var(--muted);border:1px solid rgba(255,255,255,0.03)}

    .layout{display:grid;grid-template-columns:1fr 320px;gap:18px}
    .quiz-card{background:linear-gradient(180deg, rgba(255,255,255,0.02), transparent);border-radius:var(--radius);padding:18px;border:1px solid rgba(255,255,255,0.03)}
    .quiz-hero{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
    .q-title{font-size:20px;margin:0}
    .q-desc{color:var(--muted);font-size:13px;margin-top:6px}
    .stats{display:flex;gap:10px;align-items:center}
    .pill{background:rgba(255,255,255,0.03);padding:6px 10px;border-radius:10px;font-size:13px;border:1px solid rgba(255,255,255,0.02)}

    .question-area{margin-top:12px}
    .q-meta{color:var(--muted);font-size:13px;margin-bottom:8px}
    .q-image{width:100%;height:220px;border-radius:10px;background-size:cover;background-position:center;margin-bottom:12px;border:1px solid rgba(255,255,255,0.03)}
    .q-text{font-size:18px;margin-bottom:12px}
    .options{display:flex;flex-direction:column;gap:8px}
    .opt{padding:12px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);cursor:pointer}
    .opt.correct{outline:3px solid rgba(34,197,94,0.14)}
    .opt.wrong{outline:3px solid rgba(239,68,68,0.12)}
    .disabled{pointer-events:none;opacity:0.85}

    .feedback{margin-top:10px;color:var(--muted)}
    .how-others{margin-top:16px}
    .bar{height:10px;border-radius:8px;background:rgba(255,255,255,0.04);overflow:hidden;border:1px solid rgba(255,255,255,0.02)}
    .bar-fill{height:100%;width:40%;border-radius:8px;background:linear-gradient(90deg,var(--accent),#fb923c)}
    .small{font-size:13px;color:var(--muted)}

    aside .card{background:transparent;padding:0;border:none}
    .related h4{margin:0 0 8px 0}
    .related .r-item{display:flex;gap:10px;align-items:center;padding:8px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);margin-bottom:8px}

    footer{margin-top:22px;color:var(--muted);text-align:center}

    @media (max-width:920px){.layout{grid-template-columns:1fr}.q-image{height:180px}}
  </style>
</head>
<body>
<header>
    <div class="logo">Travel Tales</div>
    <div class="header-right-group">
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="destinations.php">Destinations</a></li>
                <li><a href="forum.php">Forum</a></li>
                <li><a href="quiz.php" class="active">Quiz</a></li>
                <li><a href="blogs.php">Blogs</a></li>
                <li><a href="articles.php">Articles</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <?php if(isset($_SESSION['user'])): ?>
                <button onclick="window.location.href='profile.php'">Profile</button>
                <button onclick="window.location.href='logout.php'">Logout</button>
            <?php else: ?>
            <button onclick="window.location.href='login.php'">Login</button>
            <?php endif; ?>
        </div>
    </div>
</header>
  <div class="container">
    <div class="layout">
      <main>
        <div class="quiz-card" id="quizCard">
          <div class="quiz-hero">
            <div>
              <h1 class="q-title" id="quizTitle">Quiz Title</h1>
              <div class="q-desc" id="quizDesc">Short description of the quiz goes here.</div>
            </div>
            <div class="stats">
              <div class="pill" id="pointsPill">0 Pts</div>
              <div class="pill" id="streakPill">Streak: 0</div>
            </div>
          </div>

          <div class="question-area">
            <div class="q-meta"><span id="progress">1/10</span></div>
            <div class="q-image" id="qImage" aria-hidden="true"></div>
            <div class="q-text" id="qText">Question text will appear here.</div>

            <div class="options" id="options"></div>

            <div class="feedback" id="feedback"></div>

            <div class="how-others" id="howOthers" style="display:none">
              <div class="small">How Others Answered</div>
              <div id="othersList" style="margin-top:8px"></div>
            </div>

            <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center">
              <div class="small">Image credit: <span id="imgCredit">—</span></div>
              <div>
                <button class="pill" id="prevBtn">Previous</button>
                <button class="pill" id="nextBtn">Next Question</button>
              </div>
            </div>

          </div>
        </div>

      </main>

      <aside>
        <div class="card related">
          <h4>Other Quizzes</h4>
          <div id="relatedList"></div>

          <h4 style="margin-top:12px">Categories</h4>
          <div id="sideCats" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px"></div>
        </div>
      </aside>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="footer-column">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Live Chat</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Press</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Work with us</h4>
                <ul>
                    <li><a href="#">As a Supplier</a></li>
                    <li><a href="#">As a Content Creator</a></li>
                    <li><a href="#">As an Affiliate Partner</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.88 1.44 1.44 0 000-2.88z"/></svg></a>
                    <a href="#" title="X (Twitter)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.212 3.793 4.649-.65.177-1.354.23-2.06.088.62 1.924 2.413 3.32 4.543 3.358-1.732 1.359-3.92 2.169-6.29 2.169-.409 0-.812-.023-1.21-.07 2.236 1.434 4.893 2.271 7.734 2.271 9.284 0 14.376-7.699 14.005-14.402.995-.718 1.858-1.612 2.543-2.639z"/></svg></a>
                    <a href="#" title="YouTube"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                </div>
                <h4 style="margin-top: 20px;">Get the app</h4>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?php echo date("Y"); ?> Travel Tales. All rights reserved.</span>
            <div class="legal-links">
                <a href="#">Imprint</a>
                <a href="#">Terms & Conditions</a>
                <a href="#">Privacy Statement</a>
            </div>
            <div class="social-links">
                <a href="#" title="Facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></a>
                <a href="#" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.88 1.44 1.44 0 000-2.88z"/></svg></a>
                <a href="#" title="Twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.212 3.793 4.649-.65.177-1.354.23-2.06.088.62 1.924 2.413 3.32 4.543 3.358-1.732 1.359-3.92 2.169-6.29 2.169-.409 0-.812-.023-1.21-.07 2.236 1.434 4.893 2.271 7.734 2.271 9.284 0 14.376-7.699 14.005-14.402.995-.718 1.858-1.612 2.543-2.639z"/></svg></a>
            </div>
        </div>
    </footer>
  </div>

  <script>
    // ---------------- Sample data to mimic travelquiz.com structure ----------------
    const QUIZZES = [
      {id:'6261e01387f755000a95cacb',slug:'origins-of-liquor',title:'Origins of Liquor',desc:'Drink in the origins of your favorite beer, wine, and liquor',image:'https://images.unsplash.com/photo-1542444459-db1a3f3a9db9?auto=format&fit=crop&w=1200&q=60',credit:'ATGImages / Shutterstock',questions:[
        {q:'Where was rum invented?',opts:['East Africa','Indonesia','The West Indies','United Kingdom'],a:2,percent:[4,3,89,5]},
        {q:'Which drink originated in Mesopotamia?',opts:['Beer','Sake','Tequila','Vodka'],a:0,percent:[75,5,10,10]},
        {q:'Which country is credited with the invention of gin?',opts:['Holland','England','Germany','Spain'],a:0,percent:[52,30,10,8]}
      ]},
      {id:'demo2',slug:'famous-stadiums',title:'World\'s Largest Stadiums',desc:'Go big or go home with this stadium quiz',image:'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=60',questions:[]}
    ];

    const CATEGORIES = ['Nature','Geography','History','Food & Drink','Arts & Culture','Landmarks','General'];

    // ---------------- Helpers ----------------
    const qs = new URLSearchParams(location.search);
    const quizId = qs.get('id') || '6261e01387f755000a95cacb';
    const quiz = QUIZZES.find(x=>x.id===quizId) || QUIZZES[0];
    let qIndex = 0; let score = 0; let streak = 0;

    document.getElementById('year').textContent = new Date().getFullYear();
    document.getElementById('quizTitle').textContent = quiz.title;
    document.getElementById('quizDesc').textContent = quiz.desc || '';
    document.getElementById('pointsPill').textContent = score + ' Pts';
    document.getElementById('streakPill').textContent = 'Streak: ' + streak;

    function renderCats(){
      const top = document.getElementById('topChips'); top.innerHTML='';
      CATEGORIES.forEach(c=>{ const b=document.createElement('div'); b.className='chip'; b.textContent=c; top.appendChild(b); });
      const side = document.getElementById('sideCats'); side.innerHTML='';
      CATEGORIES.forEach(c=>{ const b=document.createElement('div'); b.className='chip'; b.textContent=c; side.appendChild(b); });
    }

    function renderRelated(){
      const r = document.getElementById('relatedList'); r.innerHTML='';
      QUIZZES.filter(x=>x.id!==quiz.id).forEach(x=>{
        const el = document.createElement('div'); el.className='r-item';
        el.innerHTML = `<div style="width:58px;height:42px;border-radius:8px;background-image:url(${x.image});background-size:cover;background-position:center"></div><div style="flex:1"><strong style='font-size:13px'>${x.title}</strong><div class='small'>${x.desc || ''}</div></div>`;
        el.onclick = ()=>{ location.search = '?id='+x.id };
        r.appendChild(el);
      })
    }

    function renderQuestion(){
      const q = quiz.questions[qIndex];
      document.getElementById('progress').textContent = `${qIndex+1}/${quiz.questions.length}`;
      document.getElementById('qImage').style.backgroundImage = `url(${quiz.image})`;
      document.getElementById('imgCredit').textContent = quiz.credit || '—';
      document.getElementById('qText').textContent = q.q;

      const opts = document.getElementById('options'); opts.innerHTML = '';
      q.opts.forEach((o,i)=>{
        const d = document.createElement('div'); d.className='opt'; d.textContent = o; d.dataset.idx = i;
        d.onclick = (ev)=>{ handleAnswer(Number(ev.currentTarget.dataset.idx)); };
        opts.appendChild(d);
      });

      document.getElementById('feedback').textContent='';
      document.getElementById('howOthers').style.display='none';
      enableNavButtons();
    }

    function handleAnswer(idx){
      const q = quiz.questions[qIndex];
      const opts = document.querySelectorAll('.opt');
      opts.forEach(x=>x.classList.add('disabled'));
      if(idx === q.a){
        opts[idx].classList.add('correct'); score += 2; streak += 1; document.getElementById('feedback').textContent = 'Correct.';
      } else {
        opts[idx].classList.add('wrong'); opts[q.a].classList.add('correct'); streak = 0; document.getElementById('feedback').textContent = 'Incorrect.';
      }
      document.getElementById('pointsPill').textContent = score + ' Pts';
      document.getElementById('streakPill').textContent = 'Streak: ' + streak;

      // show how others answered
      renderHowOthers(q);
    }

    function renderHowOthers(q){
      const container = document.getElementById('othersList'); container.innerHTML = '';
      const total = q.percent ? q.percent.reduce((a,b)=>a+b,0) : 0;
      q.opts.forEach((opt,i)=>{
        const pct = q.percent ? q.percent[i] : Math.round(Math.random()*70+5);
        const row = document.createElement('div'); row.style.marginBottom='8px';
        row.innerHTML = `<div style='display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px'><div>${opt}</div><div class='small'>${pct}%</div></div><div class='bar'><div class='bar-fill' style='width:${pct}%'></div></div>`;
        container.appendChild(row);
      });
      document.getElementById('howOthers').style.display='block';
    }

    document.getElementById('nextBtn').onclick = ()=>{
      if(qIndex < quiz.questions.length - 1){ qIndex++; renderQuestion(); window.scrollTo({top:0,behavior:'smooth'}); }
      else { alert('Quiz finished! Score: '+score+' / '+(quiz.questions.length*2)); }
    }
    document.getElementById('prevBtn').onclick = ()=>{ if(qIndex>0) { qIndex--; renderQuestion(); } }

    function enableNavButtons(){
      document.getElementById('prevBtn').disabled = (qIndex===0);
      document.getElementById('nextBtn').disabled = false;
    }

    // init
    renderCats(); renderRelated(); renderQuestion();

  </script>
</body>
</html>
