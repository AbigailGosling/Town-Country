<?php

use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportTable;
use App\Models\ReportTableColumn;
use App\Models\ReportTableLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("tandc_live")->dropIfExists('reports');
        Schema::connection("tandc_live")->dropIfExists('report_tables');
        Schema::connection("tandc_live")->dropIfExists('report_table_links');
        Schema::connection("tandc_live")->dropIfExists('report_columns');
        Schema::connection("tandc_live")->dropIfExists('report_table_column');
        Schema::connection("tandc_live")->create('reports', function (Blueprint $table) {
            $table->id();
            $table->integer("author_id")->index();
            $table->string("name");
            $table->string("mode");
            $table->timestamps();
        });
        Schema::connection("tandc_live")->create('report_tables', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("mode");
            $table->boolean("isSup");
            $table->integer("version");
            $table->timestamps();
        });
        Schema::connection("tandc_live")->create('report_table_links', function (Blueprint $table) {
            $table->id();
            $table->integer("report_id");
            $table->integer("table_id");
        });
        Schema::connection("tandc_live")->create('report_columns', function (Blueprint $table) {
            $table->id();
            $table->text("label");
            $table->string("data_type");
            $table->string("processing_type");
            $table->string("header");
            $table->string("cell");
            $table->string("footer");
            $table->text("pointers")->nullable();
            $table->text("metadata")->nullable();
        });
        Schema::connection("tandc_live")->create('report_table_column', function (Blueprint $table) {
            $table->id();
            $table->integer("table_id")->index();
            $table->integer("column_id")->index();
            $table->integer("order");
        });
        $columns = array(
            array(  
                "label"             => ["NOTE"],
                "data_type"         => "string",
                "processing_type"   => "item_type",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => NULL,
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["User"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["users.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Date Created"],
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["debits"=>["pickerSheets.date"],
                                        "credits"=>["invoice_payments.created_at"]],
                "metadata"          => ['format_from' => "Y-m-d H:i:s", 'format_to' => "d/m/Y H:i:s"],
            ),
            array(  
                "label"             => ["Date Assembled"],
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["debits"=>["pickerSheets.date_completed"],
                                        "credits"=>["this.blank"]],
                "metadata"          => ['format_from' => "Y-m-d H:i:s", 'format_to' => "d/m/Y"],
            ),
            array(  
                "label"             => ["Date Delivered"],
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["debits"=>["pickerSheets.estimated_delivery_date"],
                                        "credits"=>["this.blank"]],
                "metadata"          => ['format_from' => "d/m/Y", 'format_to' => "d/m/Y"],
            ),
            array(  
                "label"             => ["Customer"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["customers.businessname"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["ID"],
                "data_type"         => "int",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["customers.id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Sage No"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["customers.sage_no"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["debits"=>"Invoice",
                                        "credits"=>"Original Invoice"],
                "data_type"         => "id",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "",
                "pointers"          => ["pickerSheets.id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["debits"=>"Intake ID",
                                        "credits"=>"Original Intake ID"],
                "data_type"         => "id",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "",
                "pointers"          => ["debits"=>["intake.id"],
                                        "credits"=>["original_intake.id"]],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["debits"=>"Return Intake ID",
                                        "credits"=>"Return Intake ID"],
                "data_type"         => "id",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "",
                "pointers"          => ["intake.id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Transport"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => NULL,//["transport.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["debits"=>"Pallet ID",
                                        "credits"=>"Original Pallet ID"],
                "data_type"         => "id",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "",
                "pointers"          => ["debits"=>["pallet.id"],
                                        "credits"=>["original_pallet.id"]],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["debits"=>"Return Pallet ID",
                                        "credits"=>"Return Pallet ID"],
                "data_type"         => "id",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "",
                "pointers"          => ["debits"=>["pallet.id"],
                                        "credits"=>["pallet.id"]],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Species"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["species.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Group"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["cutgroups.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Cut"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["cuts.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Brand"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["brands.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Supplier"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["debits"=>["supplier.name"],
                                        "credits"=>["original_supplier.name"]],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Nationality"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["nationality.name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Temp"],
                "data_type"         => "string",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["temperature.temperature"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Cases"],
                "data_type"         => "int",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["weights.rows"],
                "metadata"          => ['filters'=>['product.unit'=>'C'],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => "G/T",
                "data_type"         => "int",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["weights.rows"],
                "metadata"          => ['filters'=>['product.unit'=>'P'],'footer'=>'array_sum'],
            ),            
            array(  
                "label"             => ["PPC"],
                "data_type"         => "int",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["weights.rows"],
                "metadata"          => ['filters'=>['product.unit'=>'PPC'],'footer'=>'array_sum'],
            ),        
            array(  
                "label"             => ["kg"],
                "data_type"         => "double",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["weights.weight_tear"],
                "metadata"          => ['footer'=>'array_sum'],
            ),        
            array(  
                "label"             => ["debits"=>"Cost/Unit",
                                        "credits"=>"Original Cost/Unit"],
                "data_type"         => "currency",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["product.cost"],
                "metadata"          => NULL,
            ),                    
            array(  
                "label"             => ["debits"=>"Cost Value",
                                        "credits"=>"Original Cost"],
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => null,
                "metadata"          => [
                    'calculate'=> [
                        'operator'=>'+','args'=> [
                            [
                                'operator'=>'*','args'=>["this.kg","this.Cost/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.PPC","this.Cost/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.PPC","this.Original Cost/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.kg","this.Original Cost/Unit"]
                            ],
                            "pickerSheets.cost"
                        ]
                    ],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => ["debits"=>"Sell/Unit",
                                        "credits"=>"Original Sell/Unit"],
                "data_type"         => "currency",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["pickerItems.price"],
                "metadata"          => NULL,
            ),                       
            array(  
                "label"             => ["debits"=>"Sell Value",
                                        "credits"=>"Credit"],
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => NULL,
                "metadata"          => [
                    'calculate'=>[
                        'operator'=>'+','args'=> [
                            [
                                'operator'=>'*','args'=>["this.kg","this.Sell/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.PPC","this.Sell/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.kg","credit_note_items.price"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.PPC","credit_note_items.price"]
                            ],
                            "pickerSheets.subTotal"
                        ]
                    ],
                    'footer'=>'array_sum'],
            ),
            array(  
                "label"             => ["debits"=>"Profit",
                                        "credits"=>"Loss"],
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => NULL,
                "metadata"          => [
                    'calculate'=>[
                        'operator'=>'+','args'=>
                        [
                            [
                                'operator'=>'-','args'=>["this.Sell Value","this.Cost Value"]
                            ],
                            [
                                'operator'=>'-','args'=>["this.Original Cost","this.Credit"]
                            ],
                        ]
                    ],'footer'=>'array_sum'],
            ), 
            array(  
                "label"             => ["debits"=>"Actual Cost/Unit",
                                        "credits"=>"Act Original Cost/Unit"],
                "data_type"         => "currency",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["debits"=>["product.price"],
                                        "credits"=>["original_product.price"]],
                "metadata"          => ['fallback'=>['this.Cost/Unit','this.Original Cost/Unit']],
            ),                    
            array(  
                "label"             => ["debits"=>"Actual Cost Value",
                                        "credits"=>"Act Original Cost"],
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => NULL,
                "metadata"          => [
                    'calculate'=> [
                        'operator'=>'+','args'=> [
                            [
                                'operator'=>'*','args'=>["this.kg","this.Actual Cost/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.PPC","this.Actual Cost/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.PPC","this.Act Original Cost/Unit"]
                            ],
                            [
                                'operator'=>'*','args'=>["this.kg","this.Act Original Cost/Unit"]
                            ],
                            "pickerSheets.actCost"
                        ]
                    ],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => ["debits"=>"Actual Sell/Unit",
                                        "credits"=>"Act Original Sell/Unit"],
                "data_type"         => "currency",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["pickerItems.price"],
                "metadata"          => NULL,
            ),                       
            array(  
                "label"             => ["debits"=>"Actual Sell Value",
                                        "credits"=>"Credit "],
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => NULL,
                "metadata"          => ['calculate'=>[
                    'operator'=>'+','args'=> [
                        [
                            'operator'=>'*','args'=>["this.kg","this.Actual Sell/Unit"]
                        ],
                        [
                            'operator'=>'*','args'=>["this.PPC","this.Actual Sell/Unit"]
                        ],
                        [
                            'operator'=>'*','args'=>["this.kg","credit_note_items.price"]
                        ],
                        [
                            'operator'=>'*','args'=>["this.PPC","credit_note_items.price"]
                        ],
                        "pickerSheets.subTotal"
                    ]
                ],
                'footer'=>'array_sum'],
            ),
            array(  
                "label"             => ["debits"=>"Actual Profit",
                                        "credits"=>"Act Loss"],
                "data_type"         => "currency",
                "processing_type"   => "calculate",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => NULL,
                "metadata"          => [
                    'calculate'=>[
                        'operator'=>'+','args'=>
                        [
                            [
                                'operator'=>'-','args'=>["this.Actual Sell Value","this.Actual Cost Value"]
                            ],
                            [
                                'operator'=>'-','args'=>["this.Act Original Cost","this.Credit "]
                            ],
                        ]
                    ],'footer'=>'array_sum']
            ), 
        );
        foreach ($columns as $column){
            try
            {
                $rc  = new ReportColumn($column);
                $rc->save();
            }
            catch (\Throwable $ex)
            {
                throw new \Exception(json_encode(($column)));
            }
        }
        $r = new Report();
        $r->author_id = 54;
        $r->name = "Product View";
        $r->mode = "product";
        $r->save();

        $r2 = new Report();
        $r2->author_id = 54;
        $r2->name = "Invoice View";
        $r2->mode = "invoice";
        $r2->save();

        $tables = array("Sales","Credits","Supplemental Sales","Supplemental Credits");
        $modes = array("debits","credits","debits","credits");
        $isSup = array(false,false,true,true);
        for ($i = 0;$i<count($tables);$i++){
            $rt = new ReportTable();
            $rt->version = 1;
            $rt->name = $tables[$i];
            $rt->mode = $modes[$i];
            $rt->isSup = $isSup[$i];
            $rt->save();

            $rtl = new ReportTableLink();
            $rtl->report_id = $r->id;
            $rtl->table_id = $rt->id;
            $rtl->save();
            $rtl = new ReportTableLink();
            $rtl->report_id = $r2->id;
            $rtl->table_id = $rt->id;
            $rtl->save();

            $order = 0;
            foreach(ReportColumn::all() as $rc) {
                $rvc = new ReportTableColumn();
                $rvc->table_id = $rt->id;
                $rvc->column_id = $rc->id;
                $rvc->order = $order;
                $rvc->save();
                $order++;
            }
        }
        DB::connection('tandc_live')->statement("DELETE FROM `tandc_live`.`report_table_column` WHERE `table_id` in (1,3) AND `column_id` IN (11,14)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("tandc_live")->dropIfExists('reports');
        Schema::connection("tandc_live")->dropIfExists('report_versions');
        Schema::connection("tandc_live")->dropIfExists('report_columns');
        Schema::connection("tandc_live")->dropIfExists('report_version_column');
    }
};
