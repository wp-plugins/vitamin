<?php

class sitemapsEditForm{
  function __construct($data, $form_submit_path, $key, $postTypesTaxonomies, $containsTypes){
  ?>
  
<div id="dashboard-widgets-wrap">
  <div id="dashboard-widgets" class="metabox-holder">

    <div class="postbox-container" style="width:50%;">
      <div class="meta-box-sortables ui-sortable" style="min-height: 50px;">

        <form action="<?php echo $form_submit_path; ?>&amp;keyToUpdate=<?php echo $key; ?>" method="post">

          <div class="postbox">
            <h3 style="cursor:text"><?php echo empty($key) ? 'Add' : 'Replace' ?>  Sitemap</h3>
            <div class="inside">

                <table style="width:100%">
                    <tr>
                        <td><label for="sitemap_title">Title:</label></td>
                        <td><input type="text" id="sitemap_title" name="sitemap_title" value="<?php echo htmlentities($data->title); ?>" style="width:100%" /></td>
                    </tr>
                    <tr>
                        <td><label for="type">Type:</label></td>
                        <td>
                            <select id="type" name="type">
                              <option value="normal"<?php if('normal' ==$data->type) echo ' selected=selected'; ?>>Normal &nbsp; </option>
                              <option value="image"<?php if('image' ==$data->type) echo ' selected=selected'; ?>>Image &nbsp; </option>
                              <option value="text"<?php if('text' ==$data->type) echo ' selected=selected'; ?>>Text &nbsp; </option>
                            </select>
                        </td>
                    </tr>

                    </tr>
                    <tr>
                        <td><label for="contains">Contain:</label></td>
                        <td>
                            <select id="contains" name="contains">
                              <?php
                                foreach ($containsTypes as $k=>$value) {
                                    echo "<option value=$k";
                                    if( 'tax_' == substr($k,0,4) ) echo " class=taxonomy";
                                    if($k == $data->contains) echo ' selected=selected';
                                    echo ">";
                                    if( ( 'tax_all' != $k ) and ( 'post_all' != $k ) ){
                                        echo ' &nbsp; ';
                                    }
                                    echo "$value</option>";
                                }
                              ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td id="limitation_title"><label for="limitation">Limitation:</label></td>
                        <td id="limitation">
                            <?php
                            echo '<label><input type=checkbox name=limitation[] value=0 id=no_limitation ';
                            if( FALSE !== strpos( '|'.$data->limitation.'|', '|0|' ) ) echo ' checked=checked';
                            echo ' /> No limitation</label>';
                            foreach ($postTypesTaxonomies as $postType=>$postData) {
                                if( $postData ){
                                    echo "<div class='$postType'>";
                                    foreach ($postData as $taxSlug=>$taxData) {
                                        if( ! empty($taxData->data) ){
                                            echo "<h4>$taxData->label</h4>";
                                            foreach ($taxData->data as $t=>$tSingleData) {
                                                echo "<label>";
                                                for($i=0;$i<$tSingleData->deep;$i++) { echo '&nbsp; &nbsp; '; }
                                                echo "<input type=checkbox name=limitation[] value=$t";
                                                if( FALSE !== strpos( '|'.$data->limitation.'|', '|'.$t.'|' ) ) echo ' checked=checked';
                                                echo " /> ".$tSingleData->title."</label>";
                                            }
                                        }
                                    }
                                    echo "</div>";
                                }
                            }
                            ?>
                            <script>
                            var xTriggered=0;
                            jQuery('#sitemap_title').keypress(function(event) {

                                // ENTER
                                if ( event.which == 13 ) return true;

                                // -
                                if ( event.which == 45 ) return true;

                                // DIGITS
                                if ( ( 48 <= event.which ) && ( event.which <= 57 ) )  return true;

                                // letters
                                if ( ( 97 <= event.which ) && ( event.which <= 122 ) )  return true;

                                // LETTERS
                                if ( ( 65 <= event.which ) && ( event.which <= 90 ) )  return true;

                                return false;
                            });

                            function refreshVisibilityLimitationsByContains(){
                                jQuery('#limitation div').css('display','none');
                                val = jQuery('#contains').val();
                                jQuery('#limitation .'+val).css('display','block');
                            }

                            refreshVisibilityLimitationsByContains();

                            function chngLimitationByContains(){
                                refreshVisibilityLimitationsByContains();
                                jQuery('#limitation input').removeAttr('checked');
                                jQuery('#limitation #no_limitation').attr('checked','checked');
                            }

                            jQuery('#contains').change(chngLimitationByContains);

                            jQuery('#limitation #no_limitation').click(function(){
                                if( 'checked' == jQuery('#limitation #no_limitation').attr('checked') ){
                                    jQuery('#limitation div input').removeAttr('checked');
                                }else{
                                    jQuery('#limitation #no_limitation').attr('checked','checked');
                                    jQuery('#limitation div input').each(function(index) {
                                        if( 'checked' == jQuery(this).attr('checked') ){
                                            jQuery('#limitation #no_limitation').removeAttr('checked');
                                        }
                                    });
                                }
                            });

                            jQuery('#limitation div input').click(function(){
                                jQuery('#limitation #no_limitation').attr('checked','checked');
                                jQuery('#limitation div input').each(function(index) {
                                    if( 'checked' == jQuery(this).attr('checked') ){
                                        jQuery('#limitation #no_limitation').removeAttr('checked');
                                    }
                                });
                            });

                            function on_type_change(){
                                if( 'image' == jQuery('#type').val() ){
                                    jQuery('#contains .taxonomy').attr('disabled','disabled');
                                    ttt = new String(jQuery('#contains').val());
                                    if( 'tax_' == ttt.substr(0,4) ){
                                        jQuery('#contains').val('post_all');
                                    }
                                }else{
                                    jQuery('#contains .taxonomy').removeAttr('disabled');
                                }
                            }

                            jQuery('#type').change( on_type_change );
                            on_type_change();

                            </script>
                            <style type="text/css">
                            #limitation strong, #limitation label{padding:0 0 3px 0;display:block}
                            #limitation{vertical-align:top;padding-top:5px}
                            #limitation h4{padding:10px 0 5px 0}
                            #limitation label:hover{ font-weight: bold; }
                            #limitation_title{vertical-align:top;width:105px;padding-top:3px}
                            </style>
                        </td>
                    </tr>
                </table>
                <p style="text-align:right">
                    <input type="submit" value="<?php echo empty($key) ? 'Add' : 'Replace'
                    ?>" title="<?php echo empty($key) ? 'Add' : 'Replace'
                    ?>" class="button-primary" />
                </p>
                <div class="clear"></div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div id="postbox-container-2" class="postbox-container" style="width:50%;">
      <div class="meta-box-sortables ui-sortable" style="min-height: 50px;">

        <div class="postbox ">
          <h3 style="cursor:text">Title</h3>
          <div class="inside">
            <p>This field represents name or title of sitemap.</p><p>Please note, that you may
            use here only letters <code>a-z</code> and <code>A-Z</code>, digits <code>0-9</code> and minus <code>-</code> sign.</p>
            <div class="clear"></div>
          </div>
        </div>

        <div class="postbox ">
          <h3 style="cursor:text">Type</h3>
          <div class="inside">
            <p>
              This plugin works with 3 types of sitemaps:
              <ul>
                <li>Normal - Clasic Google XML sitemap with links to your pages</li>
                <li>Image - Google XML image sitemap with links to your images</li>
                <li>Text - Unusual type of sitemap, just list of links in plain text</li>
              </ul>
            </p>
            <div class="clear"></div>
          </div>
        </div>

        <div class="postbox ">
          <h3 style="cursor:text">Contains</h3>
          <div class="inside">
            <p>What kind of links will sitemap contain.</p>
            <div class="clear"></div>
          </div>
        </div>

        <div class="postbox ">
          <h3 style="cursor:text">Limitation</h3>
          <div class="inside">
            <p>Every Post is connected with taxonomies like Tags and Categories. In sitemap will be just post from choosen taxonomy.</p>
            <p>For example - you may create image sitemap with post, that are in category portfolio.</p>
            <p>Limitation with custom plugins Pages or Custom Post types may work only in case you
            connect them somehow. This option is never allowed for taxonomies.</p>
            <div class="clear"></div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?php
  }
}

