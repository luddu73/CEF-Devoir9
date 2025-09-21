<div class="modal fade" id="trajetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div id="trajetModalLoading">Chargement…</div>

        <div id="trajetModalError" class="alert alert-danger d-none"></div>

        <div id="trajetModalContent" class="d-none">
          <ul class="list-unstyled">
            <li>Auteur : <strong id="m_auteur"></strong></li>
            <li>Téléphone : <a class="text-decoration-none text-reset" id="m_auteur_tel_link"><strong id="m_auteur_tel"></strong></a></li>
            <li>Email : <a class="text-decoration-none text-reset" id="m_auteur_email_link"><strong id="m_auteur_email"></strong></a></li>
            <li>Nombre total de places : <strong id="m_places"></strong></li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>
