<div class="dropslot"></div>

<script id="fileListTemplate" type="text/x-jquery-tmpl">
<ul class="nav nav-tabs nav-stacked">
<li class="active"><a href="#">${title}</a></li>
 {{each data.contents}}
<li><a class="shareLink" href="/dropbox?path={{html encodeURIComponent(path)}}">${path}</a></li>
{{/each}}
</ul>
</script>

<script>
  var data = {};
  data.apiKey = openpne.apiKey;
  data.path = '/m<?php echo $member->getId(); ?>';
  var $pushHtml;
  $.get('/api.php/dropbox/list',data,function(json){
    console.log(json);
    json.title = "マイファイルリスト"
    $pushHtml = $("#fileListTemplate").tmpl(json);
    $(".dropslot").append($pushHtml);
  });
  
</script>

<form class="well" action="/api.php/dropbox/upload" method="post" enctype="multipart/form-data">
  アップロード：<br />
  <input class="fileupload" type="hidden" name="apiKey" value="" />
  <input type="file" name="upfile" size="5" /><br />
  <br />
  <input class="btn" type="submit" value="送信" />
<br />
</form>

<script>
$("input.fileupload ").attr("value",openpne.apiKey);
</script>
