<?php

namespace pinfirelabs\pcmIntegrations\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EquipmentInventory extends AbstractExtension
{
    public function getFunctions()
    {
        $cmApiServer = \Craft::$app->getGlobals()->getSetByHandle('siteInformation')->getFieldValue('pcmDomain');

        $styleSheet = '/vendor/pinfirelabs/pcm-integrations/src/assets/main.css';
        $script = '/vendor/pinfirelabs/pcm-integrations/src/assets/js/main.js';

        return [
            new \Twig_Function(
                'equipment_inventory', 
                function() {
                    return new \Twig_Markup('
                        <div class="equipment-inventory-container">
                            <div class="error hide alert alert-danger">
                                <strong>Equipment Inventory Error!</strong> <div class="text"></div>
                            </div>
                            <div class="rest">
                                <div class="input-group">
                                    <span class="input-group-btn">
                                    <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" ></span></button>
                                    </span>
                                    <input type="text" class="form-control equip-filter" placeholder="Search for...">
                                </div>
                                <div class="loading">
                                    <span class="glyphicon glyphicon-refresh equip-spinning"></span>
                                </div>

                                <div class="treeview">

                                </div>

                                <div class="label"></div>
                            </div>
                        </div>
                    ', 'utf-8');
                }
            ),
            new \Twig_Function(
                'equipment_inventory_header',
                function() use($styleSheet, $script, $cmApiServer) {
                    return new \Twig_Markup(
                        "
                            <link rel='stylesheet' type='text/css' href='$styleSheet' />
                            <script src='https://cdnjs.cloudflare.com/ajax/libs/redux/4.0.0/redux.min.js'></script>
                            <script src='https://cdnjs.cloudflare.com/ajax/libs/redux-thunk/2.3.0/redux-thunk.min.js'></script>

                            <script>
                                window.cmApiServer = '$cmApiServer';
                            </script>

                            <script src='$script'></script>

                            <script type='text/template' class='equip-inv-line-template'>
                                <li class='list-group-item'>
                                    <div class='indent'>
                                    </div>
                                    <div class='expander'>
                                        <span class='plus glyphicon glyphicon-plus'></span>
                                        <span class='minus glyphicon glyphicon-minus'></span>
                                    </div>
                                    <span class='txt'></span>
                                    <span class='label'></span>
                                </li>
                            </script>
                        ", 
                        'utf-8'
                    );
                }
            )
        ];
    }
}