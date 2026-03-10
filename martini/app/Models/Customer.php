<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Customer
 *
 * @property int $id
 * @property string|null $businessname
 * @property string|null $tradingas
 * @property string|null $address1_1
 * @property string|null $address1_2
 * @property string|null $address1_3
 * @property string|null $address1_4
 * @property string|null $postcode_1
 * @property string|null $address2_1
 * @property string|null $address2_2
 * @property string|null $address2_3
 * @property string|null $address2_4
 * @property string|null $postcode_2
 * @property string|null $address3_1
 * @property string|null $address3_2
 * @property string|null $address3_3
 * @property string|null $address3_4
 * @property string|null $postcode_3
 * @property string|null $nameofbuyer
 * @property string|null $contactnumber
 * @property string|null $customer_email
 * @property string|null $salesman
 * @property string|null $companyregno
 * @property string|null $accounts_address_1
 * @property string|null $accounts_address_2
 * @property string|null $accounts_address_3
 * @property string|null $accounts_address_4
 * @property string|null $accounts_contact
 * @property string|null $tel_number
 * @property string|null $internal_email
 * @property string|null $credit_terms
 * @property int $due_warning
 * @property int $credit_grace
 * @property string|null $pricedefault
 * @property float|null $credit_rating
 * @property float|null $flaguplimit
 * @property float|null $current_outstanding
 * @property string|null $address1_number
 * @property string|null $address2_number
 * @property string|null $address3_number
 * @property int $override
 * @property int $credit_enabled
 * @property string|null $users
 * @property string|null $accounts_email
 * @property string|null $accounts_comments
 * @property string|null $default_salesman_id
 * @property string $businessnameDM
 * @property bool $disabled
 * @property string|null $address4_1
 * @property string|null $address4_2
 * @property string|null $address4_3
 * @property string|null $address4_4
 * @property string|null $postcode_4
 * @property string|null $address5_1
 * @property string|null $address5_2
 * @property string|null $address5_3
 * @property string|null $address5_4
 * @property string|null $postcode_5
 * @property string|null $address6_1
 * @property string|null $address6_2
 * @property string|null $address6_3
 * @property string|null $address6_4
 * @property string|null $postcode_6
 * @property string|null $address7_1
 * @property string|null $address7_2
 * @property string|null $address7_3
 * @property string|null $address7_4
 * @property string|null $postcode_7
 * @property string|null $address8_1
 * @property string|null $address8_2
 * @property string|null $address8_3
 * @property string|null $address8_4
 * @property string|null $postcode_8
 * @property string|null $address9_1
 * @property string|null $address9_2
 * @property string|null $address9_3
 * @property string|null $address9_4
 * @property string|null $postcode_9
 * @property string|null $address4_number
 * @property string|null $address5_number
 * @property string|null $address6_number
 * @property string|null $address7_number
 * @property string|null $address8_number
 * @property string|null $address9_number
 * @property bool $markup_enabled
 * @property bool $allowPrint
 * @property float|null $markup_amount
 * @property bool $delivery_day_checking
 * @property bool $delivery_day_override
 * @property int $delivery_days
 * @property string|null $sage_no
 *
 * @package App\Models
 */
class Customer extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'customers';
	public $timestamps = false;

	protected $casts = [
		'due_warning' => 'int',
		'credit_grace' => 'int',
		'credit_rating' => 'float',
		'flaguplimit' => 'float',
		'current_outstanding' => 'float',
		'override' => 'int',
		'credit_enabled' => 'int',
		'disabled' => 'bool',
		'markup_enabled' => 'bool',
		'allowPrint' => 'bool',
		'markup_amount' => 'float',
		'delivery_day_checking' => 'bool',
		'delivery_day_override' => 'bool',
		'delivery_days' => 'int',
        'override_cost_check' => 'bool',
        'cost_check_enabled' => 'bool',
	];

	protected $fillable = [
		'businessname',
		'tradingas',
		'address1_1',
		'address1_2',
		'address1_3',
		'address1_4',
		'postcode_1',
		'address2_1',
		'address2_2',
		'address2_3',
		'address2_4',
		'postcode_2',
		'address3_1',
		'address3_2',
		'address3_3',
		'address3_4',
		'postcode_3',
		'nameofbuyer',
		'contactnumber',
		'customer_email',
		'salesman',
		'companyregno',
		'accounts_address_1',
		'accounts_address_2',
		'accounts_address_3',
		'accounts_address_4',
		'accounts_contact',
		'tel_number',
		'internal_email',
		'credit_terms',
		'due_warning',
		'credit_grace',
		'pricedefault',
		'credit_rating',
		'flaguplimit',
		'current_outstanding',
		'address1_number',
		'address2_number',
		'address3_number',
		'override',
		'credit_enabled',
		'users',
		'accounts_email',
		'accounts_comments',
		'default_salesman_id',
		'businessnameDM',
		'disabled',
		'address4_1',
		'address4_2',
		'address4_3',
		'address4_4',
		'postcode_4',
		'address5_1',
		'address5_2',
		'address5_3',
		'address5_4',
		'postcode_5',
		'address6_1',
		'address6_2',
		'address6_3',
		'address6_4',
		'postcode_6',
		'address7_1',
		'address7_2',
		'address7_3',
		'address7_4',
		'postcode_7',
		'address8_1',
		'address8_2',
		'address8_3',
		'address8_4',
		'postcode_8',
		'address9_1',
		'address9_2',
		'address9_3',
		'address9_4',
		'postcode_9',
		'address4_number',
		'address5_number',
		'address6_number',
		'address7_number',
		'address8_number',
		'address9_number',
		'markup_enabled',
		'allowPrint',
		'markup_amount',
		'delivery_day_checking',
		'delivery_day_override',
		'delivery_days',
		'sage_no',
        'override_cost_check',
        'cost_check_enabled',
	];
    public function user():BelongsTo{
        return $this->belongsTo(OldUser::class,"default_salesman_id","id");
    }
    public function site():BelongsTo{
        return $this->belongsTo(Site::class,"site_id","id");
    }
}
