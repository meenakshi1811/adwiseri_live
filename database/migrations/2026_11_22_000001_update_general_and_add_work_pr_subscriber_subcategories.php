<?php

use App\Services\SubscriberSubCategoryCatalog;
use Illuminate\Database\Migrations\Migration;

/**
 * Sign-up "Sub-Category" list: "General (All Countries)" reads "General (All Countries) Visa Consultant",
 * and the missing Use Cases practice types (work visa, PR / settlement) are added.
 * The work itself lives in SubscriberSubCategoryCatalog, which the dropdown also runs once, so the
 * list is right even where this migration has not been run yet.
 */
class UpdateGeneralAndAddWorkPrSubscriberSubcategories extends Migration
{
    public function up()
    {
        app(SubscriberSubCategoryCatalog::class)->apply();
    }

    public function down()
    {
        app(SubscriberSubCategoryCatalog::class)->revert();
    }
}
