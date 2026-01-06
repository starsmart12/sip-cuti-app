<!-- <div class="modal fade" id="msgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title">Informasi Sistem</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pb-5">
        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
        <p id="msgContent" class="fs-5"></p>
        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
function showModal(pesan, redirectUrl = null) {
    document.getElementById('msgContent').innerText = pesan;
    var myModal = new bootstrap.Modal(document.getElementById('msgModal'));
    myModal.show();
    
    if(redirectUrl) {
        document.getElementById('msgModal').addEventListener('hidden.bs.modal', function () {
            window.location.href = redirectUrl;
        });
    }
}
</script> -->