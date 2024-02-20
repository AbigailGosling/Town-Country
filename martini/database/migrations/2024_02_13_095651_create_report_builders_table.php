<?php

use App\Models\ReportColumn;
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
            $table->string("label");
            $table->string("fetch_type");
            $table->string("data_type");
            $table->string("processing_type");
            $table->string("html_header");
            $table->string("html_cell");
            $table->string("html_footer");
            $table->json("pointers")->nullable();
            $table->json("metadata")->nullable();
        });
        Schema::connection("tandc_live")->create('report_version_column', function (Blueprint $table) {
            $table->id();
            $table->integer("report_version_id")->index();
            $table->integer("report_column_id")->index();
            $table->integer("order");
        });
        $columns = array(
            array(  
                "label"             => "NOTE",
                "fetch_type"        => "",
                "data_type"         => "string",
                "processing_type"   => "item_type",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%s</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => NULL,
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "User",
                "fetch_type"        => "db",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%s</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["user_name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Date Created",
                "fetch_type"        => "db",
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%s</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["date_created"],
                "metadata"          => ['format_from' => "", 'format_to' => ""],
            ),
            array(  
                "label"             => "Date Assembled",
                "fetch_type"        => "db",
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%s</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["date_assembled"],
                "metadata"          => ['format_from' => "", 'format_to' => ""],
            ),
            array(  
                "label"             => "Date Delivered",
                "fetch_type"        => "db",
                "data_type"         => "date",
                "processing_type"   => "date_format",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%s</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["date_delivered"],
                "metadata"          => ['format_from' => "", 'format_to' => ""],
            ),
            array(  
                "label"             => "Customer",
                "fetch_type"        => "db",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%s</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["customer_name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Invoice",
                "fetch_type"        => "db",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%d</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["invoice_id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Intake ID",
                "fetch_type"        => "db",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%d</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["intake_id"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Transport",
                "fetch_type"        => "db",
                "data_type"         => "string",
                "processing_type"   => "none",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%s</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["transport_name"],
                "metadata"          => NULL,
            ),
            array(  
                "label"             => "Pallet ID",
                "fetch_type"        => "db",
                "data_type"         => "int",
                "processing_type"   => "none",
                "html_header"       => "<th>%s</th>",
                "html_cell"         => "<td>%d</td>",
                "html_footer"       => "<th></th>",
                "pointers"          => ["pallet_id"],
                "metadata"          => NULL,
            ),
        );
        foreach ($columns as $column){
            $rc  = new ReportColumn($column);
            $rc->save();
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
        Schema::connection("tandc_live")->dropIfExists('report_version_columns');
    }
};
