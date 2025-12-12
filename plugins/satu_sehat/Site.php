<?php

namespace Plugins\Satu_Sehat;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('satu-sehat/encounter/(:any)', 'forwardEncounter');
        $this->route('satu-sehat/condition/(:any)', 'forwardCondition');
        $this->route('satu-sehat/observation/(:any)/(:any)', 'forwardObservation');
        $this->route('satu-sehat/procedure/(:any)', 'forwardProcedure');
        $this->route('satu-sehat/diet-gizi/(:any)', 'forwardDietGizi');
        $this->route('satu-sehat/vaksin/(:any)', 'forwardVaksin');
        $this->route('satu-sehat/clinical-impression/(:any)', 'forwardClinicalImpression');
        $this->route('satu-sehat/medication/(:any)/(:any)', 'forwardMedication');
        $this->route('satu-sehat/medication/(:any)', 'forwardMedication');
        $this->route('satu-sehat/laboratory/(:any)/(:any)', 'forwardLaboratory');
        $this->route('satu-sehat/laboratory/(:any)', 'forwardLaboratory');
        $this->route('satu-sehat/radiology/(:any)/(:any)', 'forwardRadiology');
        $this->route('satu-sehat/radiology/(:any)', 'forwardRadiology');
        $this->route('satu-sehat/forward-norawat/(:any)', 'forwardByNoRawat');
        $this->route('satu-sehat/forward-norawat', 'forwardByNoRawat');
        $this->route('satu-sehat/forward-tanggal/(:any)', 'forwardByDate');
        $this->route('satu-sehat/forward-tanggal', 'forwardByDate');
    }

    public function forwardEncounter($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getEncounter($no_rawat, false);
    }

    public function forwardCondition($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getCondition($no_rawat, false);
    }

    public function forwardObservation($no_rawat = null, $ttv = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($ttv === null && isset($_GET['ttv'])) {
            $ttv = $_GET['ttv'];
        }
        if ($no_rawat === null || $ttv === null) {
            echo json_encode(['error' => 'parameter kosong', 'no_rawat' => $no_rawat, 'ttv' => $ttv]);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getObservation($no_rawat, $ttv, false);
    }

    public function forwardProcedure($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getProcedure($no_rawat);
    }

    public function forwardDietGizi($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getDietGizi($no_rawat);
    }

    public function forwardVaksin($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getVaksin($no_rawat);
    }

    public function forwardClinicalImpression($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getClinicalImpression($no_rawat);
    }

    public function forwardMedication($no_rawat = null, $tipe = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($tipe === null && isset($_GET['tipe'])) {
            $tipe = $_GET['tipe'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        if ($tipe === null) {
            $tipe = 'request';
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getMedication((string)$no_rawat, (string)$tipe);
    }

    public function forwardCarePlan($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getCarePlan($no_rawat);
    }

    public function forwardLaboratory($no_rawat = null, $tipe = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($tipe === null && isset($_GET['tipe'])) {
            $tipe = $_GET['tipe'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        if ($tipe === null) {
            $tipe = 'result';
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getLaboratory((string)$no_rawat, (string)$tipe);
    }

    public function forwardRadiology($no_rawat = null, $tipe = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($tipe === null && isset($_GET['tipe'])) {
            $tipe = $_GET['tipe'];
        }
        if ($no_rawat === null) {
            echo json_encode(['error' => 'no_rawat kosong']);
            exit();
        }
        if ($tipe === null) {
            $tipe = 'result';
        }
        $admin = new \Plugins\Satu_Sehat\Admin($this->core);
        $admin->init();
        return $admin->getRadiology((string)$no_rawat, (string)$tipe);
    }

    public function forwardByDate($tanggal = null)
    {
        if ($tanggal === null && isset($_GET['tanggal'])) {
            $tanggal = $_GET['tanggal'];
        }
        if ($tanggal === null) {
            $tanggal = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $tanggal = date('Y-m-d');
        }
        $rows = $this->db('reg_periksa')
            ->select('no_rawat')
            ->where('reg_periksa.tgl_registrasi', $tanggal)
            ->where('stts', '!=', 'Batal')
            ->toArray();
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'display' => $r['no_rawat'],
                'url' => str_replace('/', '', $r['no_rawat'])
            ];
        }
        $encBase = '/satu-sehat/encounter/';
        $condBase = '/satu-sehat/condition/';
        $obsBase = '/satu-sehat/observation/';
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Forward Satu Sehat</title><style>body{font-family:system-ui,Arial,sans-serif} .item{border:1px solid #ddd;padding:8px;margin:8px 0} .label{font-weight:bold} pre{white-space:pre-wrap;word-wrap:break-word;background:#f7f7f7;padding:8px;border-radius:4px} .summary{margin-top:16px;border-top:2px solid #ccc;padding-top:12px}</style></head><body><h3>Proses tanggal ' . $tanggal . '</h3><div id="log"></div><div class="summary"><div class="label">Ringkasan JSON</div><pre id="summary"></pre></div><script>const list=' . json_encode($list) . ';const ttv=["tensi","nadi","respirasi","suhu"];async function call(u){try{const r=await fetch(u);return await r.text()}catch(e){return String(e)}}function toJsonOrString(t){try{return JSON.parse(t)}catch(e){try{return JSON.parse(t.replace(/`/g,""))}catch(e2){return t}}}async function run(){const log=document.getElementById("log");const summary=document.getElementById("summary");const results=[];for(const item of list){const nrDisp=item.display;const nrUrl=item.url;const container=document.createElement("div");container.className="item";container.innerHTML=`<div class="label">No Rawat: ${nrDisp}</div>`;log.appendChild(container);const encTxt=await call("' . $encBase . '"+nrUrl);const enc=toJsonOrString(encTxt);container.insertAdjacentHTML("beforeend","<div>Encounter:<pre>"+ (typeof enc==="string"?enc:JSON.stringify(enc,null,2)) +"</pre></div>");const condTxt=await call("' . $condBase . '"+nrUrl);const cond=toJsonOrString(condTxt);container.insertAdjacentHTML("beforeend","<div>Condition:<pre>"+ (typeof cond==="string"?cond:JSON.stringify(cond,null,2)) +"</pre></div>");const obsRes={};for(const t of ttv){const obsTxt=await call("' . $obsBase . '"+nrUrl+"/"+t);const obs=toJsonOrString(obsTxt);obsRes[t]=obs;container.insertAdjacentHTML("beforeend","<div>Observation ("+t+"):<pre>"+ (typeof obs==="string"?obs:JSON.stringify(obs,null,2)) +"</pre></div>")};results.push({no_rawat:nrDisp,encounter:enc,condition:cond,observation:obsRes});summary.textContent=JSON.stringify(results,null,2)}summary.textContent=JSON.stringify(results,null,2)}run()</script></body></html>';
        echo '<script>const procBase="/satu-sehat/procedure/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const procTxt=await fetch(procBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let proc;try{proc=JSON.parse(procTxt)}catch(e){try{proc=JSON.parse(procTxt.replace(/`/g,""))}catch(e2){proc=procTxt}}container.insertAdjacentHTML("beforeend","<div>Procedure:<pre>"+ (typeof proc==="string"?proc:JSON.stringify(proc,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},500)});</script>';
        echo '<script>const dietBase="/satu-sehat/diet-gizi/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const dietTxt=await fetch(dietBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let diet;try{diet=JSON.parse(dietTxt)}catch(e){try{diet=JSON.parse(dietTxt.replace(/`/g,""))}catch(e2){diet=dietTxt}}container.insertAdjacentHTML("beforeend","<div>Diet Gizi:<pre>"+ (typeof diet==="string"?diet:JSON.stringify(diet,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},800)});</script>';
        echo '<script>const vaksinBase="/satu-sehat/vaksin/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const vakTxt=await fetch(vaksinBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let vak;try{vak=JSON.parse(vakTxt)}catch(e){try{vak=JSON.parse(vakTxt.replace(/`/g,""))}catch(e2){vak=vakTxt}}container.insertAdjacentHTML("beforeend","<div>Vaksin:<pre>"+ (typeof vak==="string"?vak:JSON.stringify(vak,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1100)});</script>';
        echo '<script>const ciBase="/satu-sehat/clinical-impression/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const ciTxt=await fetch(ciBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let ci;try{ci=JSON.parse(ciTxt)}catch(e){try{ci=JSON.parse(ciTxt.replace(/`/g,""))}catch(e2){ci=ciTxt}}container.insertAdjacentHTML("beforeend","<div>Clinical Impression:<pre>"+ (typeof ci==="string"?ci:JSON.stringify(ci,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1400)});</script>';
        echo '<script>const medBase="/satu-sehat/medication/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const medTxt=await fetch(medBase+nrUrl+"/request").then(r=>r.text()).catch(e=>String(e));let med;try{med=JSON.parse(medTxt)}catch(e){try{med=JSON.parse(medTxt.replace(/`/g,""))}catch(e2){med=medTxt}}container.insertAdjacentHTML("beforeend","<div>Medication (request):<pre>"+ (typeof med==="string"?med:JSON.stringify(med,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1700)});</script>';
        echo '<script>const cpBase="/satu-sehat/care-plan/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const cpTxt=await fetch(cpBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let cp;try{cp=JSON.parse(cpTxt)}catch(e){try{cp=JSON.parse(cpTxt.replace(/`/g,""))}catch(e2){cp=cpTxt}}container.insertAdjacentHTML("beforeend","<div>Care Plan:<pre>"+ (typeof cp==="string"?cp:JSON.stringify(cp,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1700)});</script>';
        echo '<script>const labBase="/satu-sehat/laboratory/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const labTxt=await fetch(labBase+nrUrl+"/result").then(r=>r.text()).catch(e=>String(e));let lab;try{lab=JSON.parse(labTxt)}catch(e){try{lab=JSON.parse(labTxt.replace(/`/g,""))}catch(e2){lab=labTxt}}container.insertAdjacentHTML("beforeend","<div>Laboratory (result):<pre>"+ (typeof lab==="string"?lab:JSON.stringify(lab,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},2000)});</script>';
        echo '<script>const radBase="/satu-sehat/radiology/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const radTxt=await fetch(radBase+nrUrl+"/result").then(r=>r.text()).catch(e=>String(e));let rad;try{rad=JSON.parse(radTxt)}catch(e){try{rad=JSON.parse(radTxt.replace(/`/g,""))}catch(e2){rad=radTxt}}container.insertAdjacentHTML("beforeend","<div>Radiology (result):<pre>"+ (typeof rad==="string"?rad:JSON.stringify(rad,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},2300)});</script>';
        echo '<script>document.addEventListener("DOMContentLoaded",()=>{const f=document.createElement("form");f.method="get";f.action="/satu-sehat/forward-tanggal";f.style.marginBottom="12px";f.innerHTML="<label>Tanggal</label> <input type=\"date\" name=\"tanggal\" value=\"' . htmlspecialchars($tanggal, ENT_QUOTES) . '\" /> <button type=\"submit\">Proses</button>";const h3=document.querySelector("h3");if(h3&&h3.parentNode){h3.parentNode.insertBefore(f,h3);}});</script>';
        exit();
    }

    public function forwardByNoRawat($no_rawat = null)
    {
        if ($no_rawat === null && isset($_GET['no_rawat'])) {
            $no_rawat = $_GET['no_rawat'];
        }
        if ($no_rawat === null) {
            $no_rawat = '';
        }
        $list = [];
        if ($no_rawat !== '') {
            $list[] = [
                'display' => $no_rawat,
                'url' => str_replace('/', '', $no_rawat)
            ];
        }
        $encBase = '/satu-sehat/encounter/';
        $condBase = '/satu-sehat/condition/';
        $obsBase = '/satu-sehat/observation/';
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Forward Satu Sehat</title><style>body{font-family:system-ui,Arial,sans-serif} .item{border:1px solid #ddd;padding:8px;margin:8px 0} .label{font-weight:bold} pre{white-space:pre-wrap;word-wrap:break-word;background:#f7f7f7;padding:8px;border-radius:4px} .summary{margin-top:16px;border-top:2px solid #ccc;padding-top:12px}</style></head><body><form method="get" action="/satu-sehat/forward-norawat" style="margin-bottom:12px"><label>No. Rawat</label> <input type="text" name="no_rawat" value="' . htmlspecialchars($no_rawat, ENT_QUOTES) . '" /> <button type="submit">Proses</button></form><h3>Proses no_rawat ' . htmlspecialchars($no_rawat, ENT_QUOTES) . '</h3><div id="log"></div><div class="summary"><div class="label">Ringkasan JSON</div><pre id="summary"></pre></div><script>const list=' . json_encode($list) . ';const ttv=["tensi","nadi","respirasi","suhu"];async function call(u){try{const r=await fetch(u);return await r.text()}catch(e){return String(e)}}function toJsonOrString(t){try{return JSON.parse(t)}catch(e){try{return JSON.parse(t.replace(/`/g,""))}catch(e2){return t}}}async function run(){const log=document.getElementById("log");const summary=document.getElementById("summary");const results=[];for(const item of list){const nrDisp=item.display;const nrUrl=item.url;const container=document.createElement("div");container.className="item";container.innerHTML=`<div class="label">No Rawat: ${nrDisp}</div>`;log.appendChild(container);const encTxt=await call("' . $encBase . '"+nrUrl);const enc=toJsonOrString(encTxt);container.insertAdjacentHTML("beforeend","<div>Encounter:<pre>"+ (typeof enc==="string"?enc:JSON.stringify(enc,null,2)) +"</pre></div>");const condTxt=await call("' . $condBase . '"+nrUrl);const cond=toJsonOrString(condTxt);container.insertAdjacentHTML("beforeend","<div>Condition:<pre>"+ (typeof cond==="string"?cond:JSON.stringify(cond,null,2)) +"</pre></div>");const obsRes={};for(const t of ttv){const obsTxt=await call("' . $obsBase . '"+nrUrl+"/"+t);const obs=toJsonOrString(obsTxt);obsRes[t]=obs;container.insertAdjacentHTML("beforeend","<div>Observation ("+t+"):<pre>"+ (typeof obs==="string"?obs:JSON.stringify(obs,null,2)) +"</pre></div>")};results.push({no_rawat:nrDisp,enc:enc,cond:cond,obs:obsRes});summary.textContent=JSON.stringify(results,null,2);} };document.addEventListener("DOMContentLoaded",()=>{try{run()}catch(e){}});</script>';
        echo '<script>const procBase="/satu-sehat/procedure/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const procTxt=await fetch(procBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let proc;try{proc=JSON.parse(procTxt)}catch(e){try{proc=JSON.parse(procTxt.replace(/`/g,""))}catch(e2){proc=procTxt}}container.insertAdjacentHTML("beforeend","<div>Procedure:<pre>"+ (typeof proc==="string"?proc:JSON.stringify(proc,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},500)});</script>';
        echo '<script>const dietBase="/satu-sehat/diet-gizi/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const dietTxt=await fetch(dietBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let diet;try{diet=JSON.parse(dietTxt)}catch(e){try{diet=JSON.parse(dietTxt.replace(/`/g,""))}catch(e2){diet=dietTxt}}container.insertAdjacentHTML("beforeend","<div>Diet Gizi:<pre>"+ (typeof diet==="string"?diet:JSON.stringify(diet,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},800)});</script>';
        echo '<script>const vaksinBase="/satu-sehat/vaksin/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const vakTxt=await fetch(vaksinBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let vak;try{vak=JSON.parse(vakTxt)}catch(e){try{vak=JSON.parse(vakTxt.replace(/`/g,""))}catch(e2){vak=vakTxt}}container.insertAdjacentHTML("beforeend","<div>Vaksin:<pre>"+ (typeof vak==="string"?vak:JSON.stringify(vak,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1100)});</script>';
        echo '<script>const ciBase="/satu-sehat/clinical-impression/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const ciTxt=await fetch(ciBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let ci;try{ci=JSON.parse(ciTxt)}catch(e){try{ci=JSON.parse(ciTxt.replace(/`/g,""))}catch(e2){ci=ciTxt}}container.insertAdjacentHTML("beforeend","<div>Clinical Impression:<pre>"+ (typeof ci==="string"?ci:JSON.stringify(ci,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1400)});</script>';
        echo '<script>const medBase="/satu-sehat/medication/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const medTxt=await fetch(medBase+nrUrl+"/request").then(r=>r.text()).catch(e=>String(e));let med;try{med=JSON.parse(medTxt)}catch(e){try{med=JSON.parse(medTxt.replace(/`/g,""))}catch(e2){med=medTxt}}container.insertAdjacentHTML("beforeend","<div>Medication (request):<pre>"+ (typeof med==="string"?med:JSON.stringify(med,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1700)});</script>';
        echo '<script>const cpBase="/satu-sehat/care-plan/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const cpTxt=await fetch(cpBase+nrUrl).then(r=>r.text()).catch(e=>String(e));let cp;try{cp=JSON.parse(cpTxt)}catch(e){try{cp=JSON.parse(cpTxt.replace(/`/g,""))}catch(e2){cp=cpTxt}}container.insertAdjacentHTML("beforeend","<div>Care Plan:<pre>"+ (typeof cp==="string"?cp:JSON.stringify(cp,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},1700)});</script>';
        echo '<script>const labBase="/satu-sehat/laboratory/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const labTxt=await fetch(labBase+nrUrl+"/result").then(r=>r.text()).catch(e=>String(e));let lab;try{lab=JSON.parse(labTxt)}catch(e){try{lab=JSON.parse(labTxt.replace(/`/g,""))}catch(e2){lab=labTxt}}container.insertAdjacentHTML("beforeend","<div>Laboratory (result):<pre>"+ (typeof lab==="string"?lab:JSON.stringify(lab,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},2000)});</script>';
        echo '<script>const radBase="/satu-sehat/radiology/";document.addEventListener("DOMContentLoaded",()=>{setTimeout(async()=>{try{const log=document.getElementById("log");const items=Array.from(log.children);for(let i=0;i<list.length;i++){const nrUrl=list[i].url;const container=items[i];const radTxt=await fetch(radBase+nrUrl+"/result").then(r=>r.text()).catch(e=>String(e));let rad;try{rad=JSON.parse(radTxt)}catch(e){try{rad=JSON.parse(radTxt.replace(/`/g,""))}catch(e2){rad=radTxt}}container.insertAdjacentHTML("beforeend","<div>Radiology (result):<pre>"+ (typeof rad==="string"?rad:JSON.stringify(rad,null,2)) +"</pre></div>");}}}catch(e){/* ignore */}},2300)});</script>';
        exit();
    }
}
