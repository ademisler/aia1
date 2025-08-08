(function($){
  'use strict';
  const AIA = window.aia || {};

  function get(url){ return fetch(url, { credentials:'same-origin' }).then(r=>r.json()); }
  function post(url, data){ const fd=new FormData(); Object.entries(data||{}).forEach(([k,v])=>fd.append(k,v)); return fetch(url, { method:'POST', body:fd, credentials:'same-origin' }).then(r=>r.json()); }

  $(function(){ if (window.lucide && lucide.createIcons) { lucide.createIcons(); }
    // Dashboard metrics
    const $total=$('#aia-metric-total'), $low=$('#aia-metric-low'), $oos=$('#aia-metric-oos');
    if ($total.length){ get(AIA.rest+'inventory').then(res=>{ if(res && res.counts){ $total.text(res.counts.total_products||0); $low.text(res.counts.low_stock||0); $oos.text(res.counts.out_of_stock||0); } }); }
    // Low stock list
    const $ls = $('#aia-low-stock-list');
    if ($ls.length){ get(AIA.rest+'inventory/low?limit=10').then(items=>{ if(Array.isArray(items)){$ls.empty(); if(!items.length){ $ls.append('<li>No low stock items 🎉</li>'); return;} items.forEach(it=>{ $ls.append('<li>'+ (it.name||('ID '+it.id)) + ' — '+ (it.stock??'?') +' <a target="_blank" href="'+ (it.edit_url||'#') +'">Edit</a></li>'); }); }}); }
    // Settings populate
    const $prov=$('#aia-provider'), $key=$('#aia-api-key'), $th=$('#aia-low-th');
    if ($prov.length){ get(AIA.rest+'settings').then(s=>{ if(s){ if(s.ai_provider) $prov.val(s.ai_provider); if(s.api_key) $key.val(s.api_key); if(typeof s.low_stock_threshold!=='undefined') $th.val(s.low_stock_threshold); } }); }
    // Sample chart
    const c = document.getElementById('aia-chart');
    if (c && window.Chart){ const ctx=c.getContext('2d'); new Chart(ctx,{ type:'line', data:{ labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], datasets:[{ label:'Orders', data:[12,19,7,15,9,13,11], borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.15)', tension:.35, fill:true }]}, options:{ plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true } } }); }
  });

  $(document).on('submit', '#aia-chat-form', function(e){ e.preventDefault(); const $input=$('#aia-chat-input'); const msg=$input.val().trim(); if(!msg) return; $input.prop('disabled', true); post(AIA.rest + 'chat', { message: msg }).then(res=>{ alert((res && res.response) || 'OK'); }).catch(()=> alert('Request failed')).finally(()=> $input.prop('disabled', false)); });

  $(document).on('submit', '#aia-settings-form', function(e){ e.preventDefault(); const form = e.currentTarget; const data = Object.fromEntries(new FormData(form).entries()); post(AIA.ajax, { action:'aia_save_settings', nonce:AIA.nonce, ...data }).then(res=>{ if(res && res.success){ alert('Settings saved'); } else { alert('Save failed'); } }).catch(()=> alert('Save failed')); });

  $(document).on('click', '#aia-test-connection', function(){ const $btn=$(this), $res=$('#aia-test-result'); $btn.prop('disabled', true); $res.text('Testing...'); get(AIA.rest+'provider/test').then(r=>{ if(r && r.success){ $res.text('OK: '+(r.message||'')); } else { $res.text('Failed'); } }).catch(()=> $res.text('Failed')).finally(()=> $btn.prop('disabled', false)); });

})(jQuery);