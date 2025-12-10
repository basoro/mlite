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
        exit();
    }
}
