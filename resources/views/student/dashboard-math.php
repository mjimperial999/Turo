<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Turo Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alata&family=Alatsi&family=Alexandria:wght@100..900&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Alatsi', sans-serif;
      background-color: #f4f4f4;
      color: #222;
    }
    header {
      background-color: #c9000A;
      color: white;
      padding: 16px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .logo img {
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
    nav {
      display: flex;
      gap: 25px;
      align-items: center;
    }
    nav a {
      color: white;
      text-decoration: none;
      font-weight: 500;
    }
    .main {
      display: grid;
      grid-template-columns: 2fr 1fr;
      max-width: 1600px;
      margin: 0 auto;
      padding: 30px;
      gap: 30px;
    }
    .left-center {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .partitioned-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    .right {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      padding: 20px;
    }
    .highlight {
      background: url(highlight2.png) center/cover;
      color: white;
      position: relative;
      min-height: 280px;
    }
    .highlight::after {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0);
      border-radius: 10px;
    }
    .highlight .content {
      position: relative;
      z-index: 1;
    }
    .highlight h3 {
      margin: 0;
      font-size: 24px;
    }
    .btn {
      display: inline-block;
      background: #c9000A;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }
    .top-right-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgb(173, 173, 173);
      color: rgb(255, 255, 255);
    }
    .all-modules-btn {
      position: absolute;
      bottom: 20px;
      right: 20px;
    }
    .small-card {
      display: flex;
      align-items: center;
      background-color: #ecfff0;
      padding: 10px;
      border-left: 6px solid #62d462;
      border-radius: 6px;
      margin-bottom: 10px;
      font-size: 14px;
    }
    .small-card i {
      margin-right: 8px;
    }
    .calendar {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 5px;
      text-align: center;
      font-size: 14px;
    }
    .calendar div {
      padding: 8px 0;
      border-radius: 4px;
      border: 1px solid #ddd;
    }
    ul {
      padding-left: 20px;
    }
    ul li span {
      color: gray;
      font-size: 12px;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="logo.png" alt="TURO Logo" />
      <div class="logo-text">
        <span>TURO</span>
        <small>by GSCS</small>
      </div>
    </div>
    <nav>
      <a href="#">MODULES</a>
      <a href="#">PERFORMANCE</a>
      <a href="#">CALENDAR</a>
      <a href="#">INBOX</a>
      <a href="#">👤</a>
    </nav>
  </header>

  <div class="main">
    <div class="left-center">
      <div class="card highlight">
        <div class="content">
          <p>Progress: <strong style="color:#62d462;">0%</strong></p>
          <h3>MODULE 1: MEASURES OF CENTRAL TENDENCY</h3>
        </div>
        <a class="btn top-right-btn" href="#">READ →</a>
        <a class="btn all-modules-btn" href="#">ALL MODULES →</a>
      </div>

      <div class="partitioned-content">
        <div class="card">
          <h3>Next Activities</h3>
          <div class="small-card">
          <i class="fa-solid fa-clock"></i>
          <div>
          <strong>SHORT QUIZ</strong>
          <div><small>Unlocks at May 15 - 1 day left</small></div>
          </div>
          </div>

          <div class="small-card">
          <i class="fa-solid fa-clock"></i>
          <div>
          <strong>LONG QUIZ</strong>
          <div><small>Unlocks at May 20 - 6 days left</small></div>
          </div>
          </div>


          <h3>Other Activities</h3>
          <div class="small-card" style="background-color:#fff7e0;border-left-color:#fbbd08;">
            <i class="fa-solid fa-bullseye"></i> <strong>SKILL-HONING TUTORIALS</strong>
          </div>
        </div>

        <div class="card">
          <h3>Performance Analytics →</h3>
          <p><strong>Module 1 Statistics</strong></p>
          <p>Completion Status: <span style="color: #62d462;">0%</span></p>
          <p><strong>MEASURES OF CENTRAL TENDENCY</strong></p>
          <ul>
            <li>Mean - <span>0%</span></li>
            <li>Median - <span>0%</span></li>
            <li>Mode - <span>0%</span></li>
            <li>Variance - <span>0%</span></li>
            <li>Standard Deviation - <span>0%</span></li>
          </ul>
          <p><strong>PROBABILITIES</strong></p>
          <ul>
            <li>Standard - <em>NONE</em></li>
            <li>Compound - <em>NONE</em></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="right">
      <div class="card">
        <h3>Schedule<br/><small>MAY 2025</small></h3>
        <div class="calendar">
          <div>SU</div><div>MO</div><div>TU</div><div>WE</div><div>TH</div><div>FR</div><div>SA</div>
          <div></div><div></div><div></div><div>1</div><div class="active">2</div><div>3</div><div>4</div>
          <div>5</div><div>6</div><div>7</div><div class="today">8</div><div>9</div><div>10</div><div>11</div>
          <div>12</div><div>13</div><div>14</div><div>15</div><div>16</div><div>17</div><div>18</div>
          <div>19</div><div>20</div><div>21</div><div>22</div><div>23</div><div>24</div><div>25</div>
          <div>26</div><div>27</div><div>28</div><div>29</div><div>30</div><div>31</div>
        </div>
      </div>
      <div class="card">
        <h3>Upcoming Activities</h3>
        <p>No activities yet.</p>
      </div>
    </div>
  </div>
</body>
</html>
