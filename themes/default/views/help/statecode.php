<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('State List'); ?></h2>

       
    </div>
<p class="introtext"><?= lang('list_results'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                

                <div class="table-responsive">
                    <table id="statelist" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th >
                               Sr No.
                            </th>
                            <th><?= lang("State Code"); ?></th>
                            <th><?= lang("State"); ?></th>
                           
                        </tr>
                        </thead>
                        <tbody>
                          <?php if($stateList) {
                              foreach($stateList as $key => $stateValue){
                              ?>
                            <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $stateValue->code ?></td>
                                <td><?= $stateValue->name ?></td>
                            </tr>
                         <?php }
                              }else { ?>
                              <tr>
                                  <td colspan="3" class="text-cneter"> Records Not Found</td>
                              </tr>
                         <?php  }?>
                    
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready( function () {
    $('#statelist').DataTable();
} );
</script>    