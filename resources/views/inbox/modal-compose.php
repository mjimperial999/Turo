<!-- ───────────  OVERLAY MODAL  ─────────── -->
<style>
  /* backdrop */
  #composeOverlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 1000;
  }
  /* centered white box */
  #composeBox {
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      border-radius: 8px;
      width: 90%;
      max-width: 900px;                   /* wider to allow two columns */
      padding: 2rem;
      box-shadow: 0 12px 30px rgba(0,0,0,.15);
      display: flex;
      flex-direction: column;
      gap: 1rem;
  }

  /* two-column grid inside the form */
  .compose-grid {
      display: flex;
      gap: 1rem;
      height: 100%;
  }
  .left-col  { flex: 0 0 42%; display:flex; flex-direction:column; }
  .right-col { flex: 1 1 auto; display:flex; flex-direction:column; gap:.75rem; }

  .picker-list {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      gap: .25rem;
      max-height: 340px;          /* internal scroll */
      overflow-y: auto;
      border: 1px solid #ddd;
      padding: .5rem;
  }
  .picker-item {
      cursor: pointer;
      padding: .25rem .5rem;
      border-radius: 4px;
  }
  .picker-item:hover { background:#f3f3f3; }
  .role-head   { font-weight: 600; margin:.5rem 0 .25rem }
</style>

<div id="composeOverlay" onclick="if(event.target===this) closeCompose()">
  <div id="composeBox">
    <h3 style="margin:0">New Inbox</h3>

    <form id="composeForm" method="POST" action="<?= route('inbox.store'); ?>"
          style="display:flex;flex-direction:column;gap:1rem">
      <?= csrf_field(); ?>

      <!-- ========== GRID ========== -->
      <div class="compose-grid">

        <!-- LEFT → Participants input + picker -->
        <div class="left-col">
          <label style="display:flex;flex-direction:column;gap:.25rem">
            <span>Participants (comma-separated IDs)</span>
            <input id="participantField" type="text" name="participants" required>
          </label>

          <div class="picker-list">
            <?php
              $grouped  = $allUsers->groupBy('role_id');
              $roleName = [1=>'Students',2=>'Teachers',3=>'Administrators'];
              foreach ([1,2,3] as $rid):
                if (!$grouped->has($rid)) continue;
            ?>
              <div class="role-head"><?= $roleName[$rid] ?></div>
              <?php foreach ($grouped[$rid] as $u): ?>
                <div class="picker-item"
                     onclick="addID('<?= $u->user_id ?>')">
                  <?= htmlspecialchars($u->last_name . ', ' . $u->first_name) ?>
                  <small style="color:#888"> (<?= $u->user_id ?>)</small>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- RIGHT → Subject + Message -->
        <div class="right-col">
          <label style="display:flex;flex-direction:column;gap:.25rem">
            <span>Subject</span>
            <input type="text" name="subject">
          </label>

          <label style="flex:1;display:flex;flex-direction:column;gap:.25rem">
            <span>Message</span>
            <textarea name="body" rows="10" style="flex:1" required></textarea>
          </label>
        </div>
      </div>

      <!-- buttons -->
      <div style="display:flex;gap:.5rem;justify-content:flex-end">
        <button type="button" class="btn" onclick="closeCompose()">Cancel</button>
        <button class="btn btn-primary">Send</button>
      </div>
    </form>
  </div>
</div>

<script>
/* ----- helpers ----- */
function openCompose()  { document.getElementById('composeOverlay').style.display='block'; }
function closeCompose() { document.getElementById('composeOverlay').style.display='none'; }

function addID(id) {
    const field = document.getElementById('participantField');
    const ids   = field.value.split(',').map(s=>s.trim()).filter(Boolean);
    if (!ids.includes(id)) ids.push(id);
    field.value = ids.join(', ');
}
</script>
