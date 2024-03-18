<?php

use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportVersion;
use App\Models\ReportVersionColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::connection("tandc_live")->dropIfExists('report_versions');
        Schema::connection("tandc_live")->dropIfExists('report_columns');
        Schema::connection("tandc_live")->dropIfExists('report_version_column');
        Schema::connection("tandc_live")->create('reports', function (Blueprint $table) {
            $table->id();
            $table->integer("author_id")->index();
            $table->string("name");
            $table->timestamps();
        });
        Schema::connection("tandc_live")->create('report_versions', function (Blueprint $table) {
            $table->id();
            $table->integer("report_id")->index();
            $table->integer("version");
            $table->timestamps();
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
        Schema::connection("tandc_live")->create('report_version_column', function (Blueprint $table) {
            $table->id();
            $table->integer("report_version_id")->index();
            $table->integer("report_column_id")->index();
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
                "pointers"          => ["pickerSheets.date"],
                "metadata"          => ['format_from' => "Y-m-d H:i:s", 'format_to' => "d/m/Y H:i:s"],
            ),
            array(  
                "label"             => ["Date Assembled"],
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["pickerSheets.date_completed"],
                "metadata"          => ['format_from' => "Y-m-d H:i:s", 'format_to' => "d/m/Y"],
            ),
            array(  
                "label"             => ["Date Delivered"],
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "header"            => "%s",
                "cell"              => "%s",
                "footer"            => "",
                "pointers"          => ["pickerSheets.estimated_delivery_date"],
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
                "label"             => ["Invoice"],
                "data_type"         => "id",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "",
                "pointers"          => ["pickerSheets.id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => ["Intake ID"],
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
                "label"             => ["Pallet ID"],
                "data_type"         => "id",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "",
                "pointers"          => ["pallet.id"],
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
                "pointers"          => ["supplier.name"],
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
                "pointers"          => ["debits"=>["weights.rows"],
                                        "credits"=>["credit_note_items.quantity"]],
                "metadata"          => ['filters'=>['product.unit'=>'C'],'footer'=>'array_sum'],
            ),
            array(  
                "label"             => "G/T",
                "data_type"         => "int",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["debits"=>["weights.rows"],
                                        "credits"=>["credit_note_items.quantity"]],
                "metadata"          => ['filters'=>['product.unit'=>'P'],'footer'=>'array_sum'],
            ),            
            array(  
                "label"             => ["PPC"],
                "data_type"         => "int",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["debits"=>["weights.rows"],
                                        "credits"=>["credit_note_items.quantity"]],
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
                            ]
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
                            ]
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
                "metadata"          => ['calculate'=>['operator'=>'-','args'=>["this.Sell Value","this.Cost Value"]],'footer'=>'array_sum'],
            ), 
            array(  
                "label"             => ["debits"=>"Actual Cost/Unit",
                                        "credits"=>"Act Original Cost/Unit"],
                "data_type"         => "currency",
                "processing_type"   => "none",
                "header"            => "%s",
                "cell"              => "%d",
                "footer"            => "%d",
                "pointers"          => ["product.price"],
                "metadata"          => ['fallback'=>'this.Cost/Unit'],
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
                            ]
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
                                        "credits"=>"Credit"],
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
                        ]
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
                "metadata"          => ['calculate'=>['operator'=>'-','args'=>["this.Actual Sell Value","this.Actual Cost Value"]],'footer'=>'array_sum'],
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
        $r->name = "master";
        $r->save();
        $rv = new ReportVersion();
        $rv->report_id = $r->id;
        $rv->version = 1;
        $rv->save();
        $order = 0;
        foreach(ReportColumn::all() as $rc) {
            $rvc = new ReportVersionColumn();
            $rvc->report_version_id = $rv->id;
            $rvc->report_column_id = $rc->id;
            $rvc->order = $order;
            $rvc->save();
            $order++;
        }
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
