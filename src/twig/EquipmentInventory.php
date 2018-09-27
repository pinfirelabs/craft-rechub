<?php

namespace pinfirelabs\pcmIntegrations\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EquipmentInventory extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig_Function(
                'equipmentInventory', 
                function() {
                    return new \Twig_Markup(<<<'HTML'
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
HTML
                    , 'utf-8');
                }
            )
        ];
    }
}