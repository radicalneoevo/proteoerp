<table style="margin:0;width:100%;border-collapse:collapse;padding:0;">
  <tr>
    <td>
      <?php if($title || $container_tr){?>
      <table style="margin:0;width:100%;border-collapse:collapse;padding:0;">
        <tr>
          <td class="mainheader"><?php echo $title?></td>
          <td class="mainheader" align="right"><?php echo $container_tr;?></td>
        </tr>
      </table>
      <?php }?> 

      <div class="mainbackground" style="padding:2px;clear:both;">
      <table width="100%" cellpadding="1" <?php echo (isset($table_id))? 'id="'.$table_id.'"': ''; ?>>
        <tr>
<?php foreach ($headers as $column)://table-header?>
<?php if (in_array($column["type"], array("orderby","detail"))):?>
          <td class="tableheader">
            <table style="width:100%; border-collapse:collapse;">
              <tr>
                <td class="tableheader_clean"><?php echo $column["label"]?></td>
                <td class="tableheader_clean" style="width:28px">
                  <a href="<?php echo $column["orderby_asc_url"]?>"><img src="<?php echo $this->rapyd->get_elements_path('orderbyasc.gif')?>" border="0"></a><a href="<?php echo $column["orderby_desc_url"]?>"><img src="<?php echo $this->rapyd->get_elements_path('orderbydesc.gif')?>" border="0"></a>
                </td>
              </tr>
            </table>
          </td>
<?php elseif ($column["type"] == "clean"):?>
          <td <?php echo $column["attributes"]?>><?php echo $column["label"]?></td>
<?php elseif (in_array($column["type"], array("normal"))):?>
          <td class="tableheader" <?php echo $column["attributes"]?>><?php echo $column["label"]?></td>
<?php endif;?>
<?php endforeach;//table-header?>
        </tr>
<?php if (count($rows)>0)://table-rows?>
  <?php $rowcount=0;?>
<?php foreach ($rows as $row):?>
  <?php $rowcount++;?>
        <tr <?php if($rowcount % 2){ echo 'class="odd"';}else{ echo 'class="even"';} ?>>
<?php foreach ($row as $cell):?>
<?php if ($cell["type"] == "detail"):?>
          <td <?php echo $cell["attributes"]?> class="littletablerow" ><a href="<?php echo $cell["link"]?>"><?php echo $cell["field"]?><img src="<?php echo $this->rapyd->get_elements_path('elenco.gif')?>" width="16" height="16" border="0" align="absmiddle" /></a></td>
<?php elseif ($cell["type"] == "clean"):?>
          <td <?php echo $cell["attributes"]?>><?php echo $cell["field"]?></td>
<?php else:?>
          <td <?php echo $cell["attributes"]?> class="littletablerow"><?php echo $cell["field"]; echo (empty($cell["field"]))? '&nbsp;':''; ?></td>
<?php endif;?>
<?php endforeach;?>
        </tr>
<?php endforeach;?>
<?php endif;//table-rows?>
      </table>
      </div>
      <?php if($pager){?>        
      <div class="mainbackground"><div class="pagenav"><?php echo $pager;?></div></div>
      <?php }?>
      <?php if($container_bl || $container_br){?>        
      <div class="mainfooter">
        <div>
          <div style="float:left"><?php echo $container_bl?></div>
          <div style="float:right"><?php echo $container_br?></div>
        </div><div style="clear:both;"></div>
      </div>
      <?php }?>
      

    </td>
  </tr>
</table>