<?php header('X-Robots-Tag: noindex, nofollow'); header('Cache-Control: no-store'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Bosowa Transportation Dashboard</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
:root{--bg:#FFFFFF;--surface:#F4F6FA;--text:#1b2538;--muted:#5d6a82;--blue:#204685;--red:#D9252A;--line:#dde3ee;color-scheme:light;box-sizing:border-box;padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){--bg:#0f1521;--surface:#182033;--text:#e8edf7;--muted:#9aa7c0;--line:#2a3550;--blue:#5b86d6;color-scheme:dark}}
:root[data-theme="dark"]{--bg:#0f1521;--surface:#182033;--text:#e8edf7;--muted:#9aa7c0;--line:#2a3550;--blue:#5b86d6;color-scheme:dark}
*{box-sizing:border-box}
html{scroll-padding-top:env(safe-area-inset-top,0px)}
body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;display:flex;min-height:100vh}
nav{width:230px;flex:none;background:#204685;color:#fff;padding:20px 12px;display:flex;flex-direction:column;gap:6px}
nav h1{font-size:15px;margin:0 8px 16px;line-height:1.3}
nav small{display:block;font-weight:400;opacity:.75;font-size:11px}
nav button{all:unset;cursor:pointer;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;letter-spacing:.03em;color:#fff}
nav button:hover,nav button.on{background:#D9252A}
main{flex:1;min-width:0;padding:24px}
header{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px}
h2{margin:0;font-size:22px;color:var(--blue)}
.meta{font-size:12px;color:var(--muted)}
.btn{border:1.5px solid var(--blue);background:transparent;color:var(--blue);padding:8px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px}
.btn:hover:not(:disabled),.btn.on{background:var(--red);border-color:var(--red);color:#fff}
.btn:disabled{opacity:.5;cursor:not-allowed}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:20px;align-items:start}
.card{background:var(--surface);border:1.5px solid var(--line);border-top:4px solid var(--blue);border-radius:12px;padding:16px}
.card span{display:block;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.card b{display:block;font-size:26px;margin:8px 0;color:var(--blue)}
.foot{display:flex;justify-content:flex-end}
.tg{all:unset;cursor:pointer;font-size:11px;font-weight:600;color:var(--blue);padding:3px 8px;border:1px solid var(--line);border-radius:99px}
.tg:hover,.tg.on{background:var(--red);border-color:var(--red);color:#fff}
.bd{margin-top:10px;border-top:1px solid var(--line);padding-top:8px;font-size:13px}
.bd div{display:flex;justify-content:space-between;padding:3px 0}
.panel{background:var(--surface);border:1.5px solid var(--line);border-radius:12px;padding:16px;margin-bottom:20px}
.panel h3{margin:0 0 12px;font-size:15px}
.chart{position:relative;height:300px}
.tools{display:flex;flex-wrap:wrap;gap:10px;align-items:end;margin-bottom:12px}
.tools label{font-size:11px;color:var(--muted);font-weight:600;display:flex;flex-direction:column;gap:3px}
.tools input{padding:7px 9px;border:1.5px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font:inherit;font-size:13px}
.tools input:focus{outline:none;border-color:var(--red)}
.sp{flex:1}
.tw{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:12.5px;min-width:820px}
th{background:var(--blue);color:#fff;text-align:left;padding:8px 10px;white-space:nowrap}
td{padding:7px 10px;border-bottom:1px solid var(--line);vertical-align:top}
tr:hover td{background:var(--bg)}
.r{text-align:right;white-space:nowrap}
.tag{font-size:11px;font-weight:700;color:var(--blue)}
.empty{padding:60px 20px;text-align:center;color:var(--muted);border:2px dashed var(--line);border-radius:12px}
.badge{display:inline-block;background:#D9252A;color:#fff;font-size:11px;padding:2px 8px;border-radius:99px;margin-left:8px;vertical-align:middle}
@media (max-width:760px){body{flex-direction:column}nav{width:100%;flex-direction:row;overflow-x:auto;padding:10px;align-items:center}nav h1{display:none}nav button{white-space:nowrap}main{padding:16px}}
</style>
</head>
<body>
<nav id="nav"><h1>BOSOWA TRANSPORTATION<small>Monitoring Dashboard</small></h1></nav>
<main>
<header><div><h2 id="ttl"></h2><div class="meta" id="meta"></div></div><button class="btn" id="rf">⟳ Refresh</button></header>
<div id="view"></div>
</main>
<script>
const PAGES=["EXECUTIVE DASHBOARD","OTO KLINIK","MALLOMO","BOSOWA UTAMA","LAPASU INDO MARINE","LAGALIGO LOGISTIK"];
const MON=["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];
const rp=n=>"Rp "+Math.round(n).toLocaleString("id-ID");
const H=36e5,DAY=864e5;
// Semua waktu disimpan UTC (seperti API), ditampilkan sebagai WITA (UTC+8)
const iso=ms=>new Date(ms+8*H).toISOString();
const ymd=ms=>iso(ms).slice(0,10);
const dt=ms=>{if(!ms)return "-";const s=iso(ms);return s.slice(8,10)+"/"+s.slice(5,7)+"/"+s.slice(0,4)+" "+s.slice(11,16)};
// DATA DUMMY (bentuk mengikuti response API Latto) — nanti diganti data asli
function gen(){
 let a=7;const R=()=>{a|=0;a=a+0x6D2B79F5|0;let t=Math.imul(a^a>>>15,1|a);t=t+Math.imul(t^t>>>7,61|t)^t;return((t^t>>>14)>>>0)/4294967296};
 const car=[["TOYOTA","AVANZA"],["TOYOTA","INNOVA"],["HONDA","BRIO"],["DAIHATSU","XENIA"],["SUZUKI","ERTIGA"],["MITSUBISHI","XPANDER"]];
 const kg=["GANTI OLI MESIN","TAMBAH FREON","SERVICE BERKALA","REM BUNYI","TUNE UP","EVAPORATOR BOCOR"],kb=["CAT ULANG BUMPER","KETOK PINTU","POLES BODY","REPARASI FENDER"];
 const mk=["MASRI SADA","RIZAL R","ANDI T","HASAN B"],pick=x=>x[Math.floor(R()*x.length)];
 const now=Date.now(),t=new Date(now+8*H),m0=Date.UTC(t.getUTCFullYear(),t.getUTCMonth(),t.getUTCDate())-8*H,out=[];
 for(let d=-365;d<=0;d++)for(let i=0,n=5+Math.floor(R()*7);i<n;i++){
  const body=R()<.3,mi=m0+d*DAY+(8+R()*9)*H;if(mi>now)continue;
  const ke=mi+(body?2+R()*5:.5+R()*2.5)*DAY,c=pick(car);
  out.push({kat:body?"Body Repair":"General Repair",mi,ke:ke>now?null:ke,harga:body?1.2e6+R()*3.3e6:15e4+R()*75e4,
   nopol:"DD "+(1000+Math.floor(R()*8999))+" "+pick(["XA","US","AB","XAE"]),kend:c[0]+" "+c[1],kel:pick(body?kb:kg),mek:pick(mk)});
 }
 return out.sort((x,y)=>y.mi-x.mi);
}
let ROWS=[],SRC="",ERR="",WARN="";
const esc=x=>String(x).replace(/[&<>"]/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c]));
let cur=1,chart=null,stamp=new Date(),from="",to="",shown=25;
const nav=document.getElementById("nav");
PAGES.forEach((p,i)=>{const b=document.createElement("button");b.textContent=p;b.onclick=()=>{cur=i;render()};nav.appendChild(b)});
function stats(){
 const td=ymd(Date.now()),mo=td.slice(0,7),z=()=>({"General Repair":0,"Body Repair":0});
 const s={ut:z(),um:z(),rt:z(),rm:z(),tr:{}};
 ROWS.forEach(r=>{const d=ymd(r.mi);if(d===td)s.ut[r.kat]++;if(d.startsWith(mo))s.um[r.kat]++;
  if(r.ke){const k=ymd(r.ke);if(k===td)s.rt[r.kat]+=r.harga;if(k.startsWith(mo))s.rm[r.kat]+=r.harga;s.tr[k.slice(0,7)]=(s.tr[k.slice(0,7)]||0)+r.harga}});
 return s;
}
function render(){
 [...nav.querySelectorAll("button")].forEach((b,i)=>b.classList.toggle("on",i===cur));
 document.getElementById("ttl").innerHTML=PAGES[cur]+(cur===1&&SRC==="demo"?'<span class="badge">DATA DUMMY</span>':"");
 const v=document.getElementById("view");
 if(chart){chart.destroy();chart=null}
 document.getElementById("meta").textContent=cur===1?"PT. OTO Garage Indonesia · Diperbarui "+stamp.toLocaleString("id-ID")+(WARN?" · ⚠ "+WARN:""):"";
 if(cur!==1){v.innerHTML='<div class="empty">Halaman ini akan dikembangkan pada tahap berikutnya.</div>';return}
 if(ERR){v.innerHTML='<div class="empty">Gagal memuat data: '+esc(ERR)+'</div>';return}
 if(!SRC){v.innerHTML='<div class="empty">Memuat data…</div>';return}
 const s=stats(),sum=o=>o["General Repair"]+o["Body Repair"];
 const cards=[["Unit Masuk Hari Ini",s.ut,0],["Unit Masuk Bulan Ini",s.um,0],["Revenue Hari Ini",s.rt,1],["Revenue MTD",s.rm,1]];
 const f=(n,m)=>m?rp(n):n.toLocaleString("id-ID")+" unit";
 v.innerHTML='<div class="grid">'+cards.map(c=>'<div class="card"><span>'+c[0]+'</span><b>'+f(sum(c[1]),c[2])+'</b><div class="foot"><button class="tg">Rincian ▾</button></div><div class="bd" hidden><div><span style="text-transform:none;letter-spacing:0">General Repair</span><b style="font-size:13px;margin:0">'+f(c[1]["General Repair"],c[2])+'</b></div><div><span style="text-transform:none;letter-spacing:0">Body Repair</span><b style="font-size:13px;margin:0">'+f(c[1]["Body Repair"],c[2])+'</b></div></div></div>').join("")+'</div>'
 +'<div class="panel"><h3>Trend Revenue Bulanan</h3><div class="chart"><canvas id="cv"></canvas></div></div>'
 +'<div class="panel"><h3>Daftar Service</h3><div class="tools"><label>Dari tanggal masuk<input type="date" id="f" value="'+from+'"></label><label>Sampai<input type="date" id="t" value="'+to+'"></label><button class="btn" id="rs">Reset</button><span class="sp"></span><button class="btn" id="ex">⤓ Export</button></div><div class="meta" id="cnt" style="margin-bottom:8px"></div><div class="tw"><table><thead><tr><th>Tgl Masuk</th><th>Tgl Keluar</th><th>No. Pol</th><th>Kendaraan</th><th>Kategori</th><th>Keluhan</th><th>Mekanik</th><th class="r">Harga</th></tr></thead><tbody id="tb"></tbody></table></div><p style="text-align:center;margin:12px 0 0"><button class="btn" id="mr">Tampilkan 25 lagi</button></p></div>';
 v.querySelectorAll(".card").forEach(c=>{const b=c.querySelector(".tg"),d=c.querySelector(".bd");b.onclick=()=>{d.hidden=!d.hidden;b.classList.toggle("on",!d.hidden);b.textContent=d.hidden?"Rincian ▾":"Rincian ▴"}});
 const cs=getComputedStyle(document.documentElement),n=new Date(Date.now()+8*H),keys=[];
 for(let i=11;i>=0;i--){const d=new Date(Date.UTC(n.getUTCFullYear(),n.getUTCMonth()-i,1));keys.push(d.toISOString().slice(0,7))}
 Chart.defaults.color=cs.getPropertyValue("--muted").trim();
 chart=new Chart(document.getElementById("cv"),{type:"bar",data:{labels:keys.map(k=>MON[+k.slice(5)-1]+" "+k.slice(2,4)),datasets:[{label:"Revenue",data:keys.map(k=>s.tr[k]||0),backgroundColor:cs.getPropertyValue("--blue").trim()}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>rp(c.raw)}}},scales:{y:{ticks:{callback:x=>(x/1e6)+" jt"}}}}});
 const fi=document.getElementById("f"),ti=document.getElementById("t");
 fi.onchange=()=>{from=fi.value;shown=25;draw()};ti.onchange=()=>{to=ti.value;shown=25;draw()};
 document.getElementById("rs").onclick=()=>{from=to="";fi.value=ti.value="";shown=25;draw()};
 document.getElementById("mr").onclick=()=>{shown+=25;draw()};
 document.getElementById("ex").onclick=exportCsv;
 draw();
}
const flt=()=>ROWS.filter(r=>{const d=ymd(r.mi);return(!from||d>=from)&&(!to||d<=to)});
function draw(){
 const a=flt(),tb=document.getElementById("tb");
 tb.innerHTML=a.slice(0,shown).map(r=>'<tr><td>'+dt(r.mi)+'</td><td>'+dt(r.ke)+'</td><td>'+esc(r.nopol)+'</td><td>'+esc(r.kend)+'</td><td class="tag">'+r.kat+'</td><td>'+esc(r.kel)+'</td><td>'+esc(r.mek)+'</td><td class="r">'+rp(r.harga)+'</td></tr>').join("")||'<tr><td colspan="8" style="text-align:center;color:var(--muted)">Tidak ada data pada rentang ini</td></tr>';
 document.getElementById("cnt").textContent="Menampilkan "+Math.min(shown,a.length)+" dari "+a.length+" data (terbaru di atas)";
 document.getElementById("mr").style.display=shown<a.length?"":"none";
}
function exportCsv(){
 const q=x=>'"'+String(x).replace(/"/g,'""')+'"';
 const l=["Tanggal Masuk;Tanggal Keluar;No Pol;Kendaraan;Kategori;Keluhan;Mekanik;Harga"].concat(flt().map(r=>[dt(r.mi),dt(r.ke),r.nopol,r.kend,r.kat,r.kel,r.mek,Math.round(r.harga)].map(q).join(";")));
 try{const a=document.createElement("a");a.href=URL.createObjectURL(new Blob(["\ufeff"+l.join("\r\n")],{type:"text/csv"}));a.download="daftar-service-oto-klinik.csv";a.click()}catch(e){alert("Export diblokir di mode pratinjau; akan berfungsi di versi asli.")}
}
const rf=document.getElementById("rf");
rf.onclick=async()=>{rf.disabled=true;rf.textContent="Memperbarui…";await load(true);let s=30;const t=setInterval(()=>{rf.textContent="Tunggu "+s+" dtk";if(--s<0){clearInterval(t);rf.disabled=false;rf.textContent="⟳ Refresh"}},1000)};
async function load(force){
 ERR="";WARN="";
 try{const r=await fetch("api.php"+(force?"?refresh=1":""),{cache:"no-store"}),j=await r.json();
  if(!r.ok||j.error)throw new Error(j.error||"HTTP "+r.status);
  SRC=j.mode;ROWS=j.mode==="demo"?gen():j.rows;stamp=new Date(j.fetched_at*1000);WARN=j.warning||""}
 catch(e){ERR=e.message}
 render();
}
render();load(false);
</script>
</body>
</html>
