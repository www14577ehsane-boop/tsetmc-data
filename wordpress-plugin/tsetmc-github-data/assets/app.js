(function(){
let data=[],filtered=[],page=1;const per=50;
const $=s=>document.querySelector(s),esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
async function init(){try{const r=await fetch(TGH.api),d=await r.json();if(!r.ok)throw new Error(d.message||'خطا');data=d.symbols||[];filtered=data;render();$('#tgh-status').textContent=(d.total||data.length)+' نماد | آخرین بروزرسانی: '+(d.updated_at||'-')}catch(e){$('#tgh-status').textContent='خطا: '+e.message}}
function render(){
 const pages=Math.max(1,Math.ceil(filtered.length/per));if(page>pages)page=1;
 const slice=filtered.slice((page-1)*per,page*per);
 $('#tgh-body').innerHTML=slice.map((x,i)=>`<tr><td><button class="tgh-symbol" data-i="${(page-1)*per+i}">${esc(x.symbol)}</button></td></tr>`).join('')||'<tr><td>نمادی پیدا نشد.</td></tr>';
 const p=$('#tgh-pages');p.innerHTML='';
 for(let i=1;i<=Math.min(pages,10);i++){let b=document.createElement('button');b.textContent=i;if(i===page)b.className='active';b.onclick=()=>{page=i;render()};p.appendChild(b)}
}
function show(x){
 const fields=[['نام شرکت',x.company_name],['بازار',x.market],['آخرین قیمت',x.last_price],['قیمت پایانی',x.closing_price],['درصد تغییر',x.change_percent]];
 let h=`<h2>${esc(x.symbol)}</h2><p>${esc(x.company_name||'')}</p><div class="tgh-grid">`;
 fields.forEach(a=>h+=`<div class="tgh-box"><b>${a[0]}</b>${esc(a[1]??'-')}</div>`);h+='</div>';
 if(x.holders&&x.holders.length){h+='<h3>سهامداران عمده</h3>';x.holders.forEach(z=>h+=`<div class="tgh-holder"><b>${esc(z.name||'-')}</b><br>درصد مالکیت: ${esc(z.percent??'-')}٪</div>`)}
 else h+='<p class="tgh-muted">اطلاعات سهامداران برای این نماد در آخرین بروزرسانی دریافت نشده است.</p>';
 $('#tgh-detail').innerHTML=h;$('#tgh-modal').hidden=false;
}
$('#tgh-search').addEventListener('input',e=>{let q=e.target.value.trim().toLowerCase();filtered=data.filter(x=>(x.symbol+' '+(x.company_name||'')).toLowerCase().includes(q));page=1;render()});
$('#tgh-body').onclick=e=>{let b=e.target.closest('[data-i]');if(b)show(filtered[+b.dataset.i])};
$('#tgh-close').onclick=()=>$('#tgh-modal').hidden=true;$('#tgh-modal').onclick=e=>{if(e.target===$('#tgh-modal'))$('#tgh-modal').hidden=true};
init();
})();