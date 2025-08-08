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

})(jQuery);