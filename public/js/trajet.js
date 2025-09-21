(function () {
  const modalEl = document.getElementById('trajetModal');
  if (!modalEl) return; // Modal not present on the page

  const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

  const $loading = document.getElementById('trajetModalLoading');
  const $error   = document.getElementById('trajetModalError');
  const $content = document.getElementById('trajetModalContent');

  const $m_auteur   = document.getElementById('m_auteur');
  const $m_auteur_tel= document.getElementById('m_auteur_tel');
  const $m_auteur_tel_link= document.getElementById('m_auteur_tel_link');
  const $m_auteur_email= document.getElementById('m_auteur_email');
  const $m_auteur_email_link= document.getElementById('m_auteur_email_link');
  const $m_places   = document.getElementById('m_places');

  function resetModal() {
    $loading.classList.remove('d-none');
    $error.classList.add('d-none');
    $content.classList.add('d-none');
    $error.textContent = '';
  }

  function formatPhone(num) {
    if (!num) return '';
    const digits = num.replace(/\D/g, '');
    if (digits.length === 10) {
      return digits.replace(/(\d{2})(?=\d)/g, '$1 ').trim(); 
    }
    return num;
  }

  async function openTrajet(id) {
    resetModal();
    bsModal.show();

    try {
        const url = `/api/detail/${id}`;
        //console.log('FETCH ->', url);
        const res = await fetch(url, { headers: { 'Accept':'application/json' } });

        //console.log('status:', res.status, 'ok:', res.ok, 'ctype:', res.headers.get('content-type'));

        if (!res.ok) {
            const text = await res.text().catch(()=>'');
            //console.warn('response(not ok):', text);
            $loading.classList.add('d-none');
            $error.textContent = res.status === 404 ? "Trajet introuvable" :
                                res.status === 401 ? "Non authentifié" :
                                `Erreur (${res.status})`;
            $error.classList.remove('d-none');
            return;
        }

        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            const text = await res.text();
            //console.warn('response(html?):', text.slice(0, 300));
            $loading.classList.add('d-none');
            $error.innerHTML = `La réponse n'est pas du JSON. ${
            text.includes('<html') ? 'Redirection probable vers une page HTML (login ?).' : ''
            }`;
            $error.classList.remove('d-none');
            return;
        }

        const data = await res.json();
        console.log('json:', data);
        
        $m_places.textContent     = (data.places ?? '').toString();
        $m_auteur.textContent     = data.auteur || '';
        $m_auteur_tel.textContent = formatPhone(data.auteur_tel) || '';
        $m_auteur_email.textContent= data.auteur_email || '';
        $m_auteur_email_link.href    = data.auteur_email ? `mailto:${data.auteur_email}` : '';
        $m_auteur_tel_link.href      = data.auteur_tel ? `tel:${data.auteur_tel}` : '';

        $loading.classList.add('d-none');
        $content.classList.remove('d-none');
        } catch (err) {
            console.error('FETCH ERROR', err);
            $loading.classList.add('d-none');
            $error.textContent = "Erreur réseau";
            $error.classList.remove('d-none');
    }
  }

  document.addEventListener('click', function (ev) {
    const btn = ev.target.closest('.btn-detail');
    if (!btn) return;
    ev.preventDefault();
    const id = btn.getAttribute('data-id');
    if (id) openTrajet(id);
  });
})();
