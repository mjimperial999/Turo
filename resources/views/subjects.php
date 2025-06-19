<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Select Subject - TURO</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alatsi&family=Alexandria:wght@100..900&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Alatsi', sans-serif;
      background-color: #fff;
      color: #222;
    }

    header {
      background-color: #c9000A;
      color: white;
      padding: 16px 32px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    header img {
      height: 40px;
    }

    .logo-text {
      display: flex;
      flex-direction: column;
      line-height: 1;
    }

    .logo-text span {
      font-weight: 800;
      font-size: 22px;
    }

    .logo-text small {
      font-size: 12px;
      color: #fbbd08;
      font-weight: 600;
      margin-left: 28px;
    }

    main {
      display: flex;
      gap: 32px;
      padding: 32px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .subjects {
      flex: 3;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }

    .subject-card {
      position: relative;
      height: 180px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0);
      cursor: pointer;
      transition: transform 0.2s;
      background: #cccccc;
    }

    .subject-card:hover {
      transform: scale(1.03);
    }

    .subject-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .subject-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      color: white;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
    }

    .subject-title {
      font-size: 18px;
      font-weight: 600;
      margin: 0;
    }

    .subject-code {
      font-size: 14px;
      color: #eee;
    }

    aside {
      flex: 1;
      background-color: #f9f9f9;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      max-height: fit-content;
    }

    aside h3 {
      margin-top: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .empty-todo {
      color: #aaa;
      font-size: 14px;
      margin-top: 10px;
    }

    /* Modal Styling */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(2px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .modal-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      max-width: 600px;
      width: 90%;
      font-family: 'Poppins', sans-serif;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }

    .modal-box h2 {
      margin-top: 0;
    }

    .modal-box p {
      font-size: 14px;
      margin: 10px 0;
    }

    .modal-box label {
      font-size: 14px;
      display: block;
    }

    .modal-box button {
      background-color: #fbbd08;
      border: none;
      color: white;
      font-weight: bold;
      padding: 10px 20px;
      font-size: 14px;
      border-radius: 6px;
      cursor: pointer;
    }

    .modal-box button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  </style>
</head>
<body>

  <header>
    <img src="logo.png" alt="TURO Logo">
    <div class="logo-text">
      <span>TURO</span>
      <small>by GSCS</small>
    </div>
  </header>

  <main>
    <section class="subjects">
      <div class="subject-card" onclick="selectSubject('math')">
        <img src="math.png" alt="Math Image" />
        <div class="subject-overlay">
          <div class="subject-title">Mathematics</div>
          <div class="subject-code">SY 2025–2026</div>
          <small>1st Quarter</small>
        </div>
      </div>
    </section>
    <aside>
      <h3>To Do</h3>
      <div class="empty-todo">No tasks yet.</div>
    </aside>
  </main>

  <!-- Confidentiality Agreement Modal -->
  <div id="confidentialityModal" class="modal-overlay">
    <div class="modal-box">
      <h2>Confidentiality Agreement</h2>
      <p>
        The Turo Tutorship Platform collects and processes certain user data to provide personalized educational
        content and gamified learning experiences. This includes your quiz scores, learning progress, and activity logs.
      </p>
      <p>
        By continuing, you acknowledge and consent to the collection of this data for the purpose of improving the
        educational experience and contributing to academic research.
      </p>
      <p>
        All personal data will be securely stored and handled only by authorized team members involved in the project.
        Your personal identity will not be revealed in any public report, paper, or publication related to this system.
      </p>
      <p>
        We are committed to protecting your privacy and will never share your information with outside parties.
      </p>
      <p>If you agree with this policy, please confirm below.</p>

      <form id="consentForm">
        <label><input type="radio" name="consent" value="agree" /> I understand and agree to the confidentiality and data privacy policy.</label><br><br>
        <label><input type="radio" name="consent" value="disagree" /> I do not understand and agree to the confidentiality and data privacy policy.</label><br><br>
        <button type="submit" id="continueBtn" disabled>CONTINUE</button>
      </form>
    </div>
  </div>

  <script>
    console.log('Hello');
    
    function selectSubject(subject) {
      localStorage.setItem("selectedSubject", subject);
      window.location.href = `dashboard-math.html?subject=${subject}`;
    }

    window.addEventListener('DOMContentLoaded', function () {
      const modal = document.getElementById('confidentialityModal');
      const continueBtn = document.getElementById('continueBtn');
      const form = document.getElementById('consentForm');

      modal.style.display = 'flex'; // Show modal on load

      form.addEventListener('change', () => {
        const selected = form.querySelector('input[name="consent"]:checked');
        continueBtn.disabled = !selected || selected.value !== 'agree';
      });

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const selected = form.querySelector('input[name="consent"]:checked');
        if (selected && selected.value === 'agree') {
          modal.style.display = 'none';
        } else {
          alert('You must agree to continue.');
        }
      });
    });
  </script>

</body>
</html>
