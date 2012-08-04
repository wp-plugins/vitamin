<?php if(empty($form_submit_path)) exit; ?>
<div id="dashboard-widgets-wrap">
  <div id="dashboard-widgets" class="metabox-holder">
    <div class="postbox-container" style="width:50%;"><div class="meta-box-sortables ui-sortable" style="min-height: 50px;">

      <form action="<?php echo $form_submit_path; ?>" method="post">

        <div class="postbox">
          <h3 style="cursor:text"><?php echo ( empty($_GET['key']) ) ? 'Add' : 'Replace' ?> RFI Attack Block</h3>
          <div class="inside">

            <table style="width:100%">
                <tr>
                    <td><label for="orgUrl">URL to File:</label></td>
                    <td><input type="text" id="orgUrl" name="orgUrl" value="<?php echo $data->orgUrl; ?>" style="width:100%" /></td>
                </tr>
            </table>
            <p style="text-align:right">
                <input type="submit" value="Add Or Replace" title="Add Or Replace" class="button-primary" />
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
        <h3 style="cursor:text">URL to File</h3>
        <div class="inside">
          <p>
            URL path to file, which will be blocked.
            <br />
            Example: <code>%anything%/phpmyadmin/%anything%</code>
          </p>
          <p>
            You may use expression <code>%anything%</code> in this field - it means any string.
          </p>
          <p>
            If this field begins with question mark "<code>?</code>", than blocking
            rules are applied to query string, that means string after question mark "<code>?</code>" character in URL.
            <br />
            Please note, that rules for query string may not work if there is character sharp "<code>#</code>" in URL.
          </p>
          <div class="clear"></div>
        </div>
      </div>

    </div>
  </div>
</div>
