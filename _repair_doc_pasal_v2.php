<?php
/**
 * Repair v2 driver: peta section -> pasal template, susun ulang, verifikasi, tulis.
 */
require __DIR__."/_repair_v2_part1.php";
echo $apply ? "MODE: APPLY\n\n" : "MODE: DRY-RUN\n\n";
$plan = [];
foreach ($ids as $id) {
  $row = DB::table("documents")->where("id", $id)->first();
  if (!$row) { echo "== DOC $id TIDAK ADA\n\n"; continue; }
  $body = json_decode((string)$row->body_content, true);
  if (!is_array($body)) { echo "== DOC $id body rusak\n\n"; continue; }
  $pages = $body["pages"] ?? [];
  if (!$pages) { echo "== DOC $id tanpa pages\n\n"; continue; }
  $head = implode(" ", array_map("v2_norm", array_slice($pages, 0, 1)));
  $src = v2_norm((string)($row->title ?? ""))." ".v2_norm(implode("\n", $pages));
  $tplKey = v2_detect_key($head, $src);
  if (!$tplKey) { echo "== DOC $id template tak dikenal\n\n"; continue; }
  $tpl = App\Data\ContractTemplates::find($tplKey);
  $tplIsi = $tpl["body_content"]["isi"] ?? [];
  $nPasal = count($tplIsi);
  echo "== DOC $id | ".mb_substr((string)$row->title,0,55)." | {$row->status} | $tplKey | hal=".count($pages)."\n";
  $cover = 0;
  foreach ($pages as $pi => $pg) {
    $pn = v2_norm($pg);
    if (str_contains($pn,"JASA COLOCATION")||str_contains($pn,"JASA MANAGED SERVICE")||str_contains($pn,"JASA SOHO")||str_contains($pn,"KONTRAK PAYUNG")||str_contains($pn,"JUAL KEMBALI JASA LAYANAN AKSES INTERNET")) { $cover = $pi+1; break; }
  }
  $blocks = [];
  foreach (array_slice($pages, $cover) as $pg) { foreach (v2_split_blocks($pg) as $b) $blocks[] = $b; }
  $firstHead = null;
  foreach ($blocks as $bi => $b) { $nn=null;
    if (in_array($b["tag"],["p","h1","h2","h3","h4","h5","h6"],true) && v2_is_pasal_heading($b["text"],$nn)) { $firstHead=$bi; break; } }
  if ($firstHead===null) { echo "   tanpa heading — lewati\n\n"; continue; }
  $endHead = count($blocks);
  foreach ($blocks as $bi => $b) {
    if ($bi <= $firstHead) continue; $t=$b["text"];
    if (preg_match("/^(hormat kami|tertanda|ditandatangani di|mengetahui|tanda tangan|lampiran\\b)/iu",$t)||preg_match("/(dibuat rangkap|materai cukup|demikian perjanjian ini)/iu",$t)) { $endHead=$bi; break; } }
  $preBlocks=array_slice($blocks,0,$firstHead);
  $postBlocks=array_slice($blocks,$endHead);
  $secBlocks=array_slice($blocks,$firstHead,$endHead-$firstHead);
  $rawSections=[]; $cur=null;
  foreach ($secBlocks as $b) { $nn=null;
    if (in_array($b["tag"],["p","h1","h2","h3","h4","h5","h6"],true) && v2_is_pasal_heading($b["text"],$nn)) {
      if ($cur!==null) $rawSections[]=$cur; $cur=["num"=>$nn,"blocks"=>[$b]]; continue; }
    if ($cur===null) { $preBlocks[]=$b; continue; }
    $cur["blocks"][]=$b; }
  if ($cur!==null) $rawSections[]=$cur;
  $sections=[];
  foreach ($rawSections as $s) { if (count($s["blocks"])==1) continue; $sections[]=$s; }
  echo "   section: ".count($sections)." (mentah ".count($rawSections).") | pre=".count($preBlocks)." post=".count($postBlocks)."\n";
  require __DIR__."/_repair_v2_part2.php";
  require __DIR__."/_repair_v2_part3.php";
  if ($errs) { echo "   VERIFIKASI: GAGAL\n"; foreach ($errs as $e) echo "      - $e\n"; echo "   -> TIDAK ditulis\n\n"; continue; }
  echo "   VERIFIKASI: LULUS -> siap ditulis\n\n";
  $newBody=$body;
  $newBody["pages"]=array_merge(array_slice($pages,0,$cover),[implode("\n",$newHtml)]);
  $plan[$id]=["body"=>$newBody,"orig"=>$body];
}
echo str_repeat("=",64)."\n";
echo "RINGKASAN: ".count($plan)." siap, ".(count($ids)-count($plan))." dilewati.\n";
if (!$apply) { echo "MODE DRY-RUN — tidak ada data yang ditulis.\n"; exit(0); }
if (!$plan) { echo "Tidak ada yang perlu ditulis.\n"; exit(0); }
if (!is_dir($backupDir)) mkdir($backupDir,0755,true);
foreach ($plan as $id => $item) {
  $bf=$backupDir."/doc-".$id.".json";
  if (!file_exists($bf)) { file_put_contents($bf, json_encode($item["orig"],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)); echo "BACKUP doc $id -> baru\n"; }
  else echo "BACKUP doc $id sudah ada — tidak ditimpa\n";
  DB::table("documents")->where("id",$id)->update(["body_content"=>json_encode($item["body"],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
  $fresh=DB::table("documents")->where("id",$id)->first();
  $chk=json_decode((string)$fresh->body_content,true);
  preg_match_all("/pasal(?:\\s|\\x{00A0}|&nbsp;|&#160;)*(\\d+)/iu",implode("\n",$chk["pages"]??[]),$mm);
  $seq=$mm[1]; $ok=($seq===array_map("strval",range(1,count($seq))));
  echo "TULIS doc $id | heading=".implode(",",$seq)." | ".($ok?"OK":"PERIKSA!")."\n";
}
echo "Selesai. Pulihkan: php _restore_doc_repair.php <id>\n";
