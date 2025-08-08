(function($){
  'use strict';
  const AIA = window.aia || {};

  function get(url, params){
    return fetch(url + (params||''), { credentials:'same-origin' }).then(r=>r.json());
  }
  function post(url, data){
    const fd = new FormData();
    Object.entries(data||{}).forEach(([k,v])=>fd.append(k,v));
    return fetch(url, { method:'POST', body:fd, credentials:'same-origin' }).then(r=>r.json());
  }

  $(function(){
    // Initialize lucide icons if available
    if (window.lucide && lucide.createIcons) {
      lucide.createIcons();
    }
  });

  $(document).on('submit', '#aia-chat-form', function(e){
    e.preventDefault();
    const $input = $('#aia-chat-input');
    const msg = $input.val().trim();
    if(!msg) return;
    $input.prop('disabled', true);
    post(AIA.rest + 'chat', { message: msg })
      .then(res => {
        alert((res && res.response) || 'OK');
      })
      .catch(()=> alert('Request failed'))
      .finally(()=> $input.prop('disabled', false));
  });

  $(document).on('submit', '#aia-settings-form', function(e){
    e.preventDefault();
    const form = e.currentTarget;
    const data = Object.fromEntries(new FormData(form).entries());
    post(AIA.ajax, { action:'aia_save_settings', nonce:AIA.nonce, ...data }).then(res=>{
      if(res && res.success){ alert('Settings saved'); } else { alert('Save failed'); }
    }).catch(()=> alert('Save failed'));
  });

})(jQuery);