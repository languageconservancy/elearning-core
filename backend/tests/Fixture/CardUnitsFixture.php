<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CardUnitsFixture
 *
 */
class CardUnitsFixture extends TestFixture
{

	/**
	 * Fields
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'card_id' => ['type' => 'integer', 'length' => 10, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => 'ID of the card in question', 'precision' => null, 'autoIncrement' => null],
		'unit_id' => ['type' => 'integer', 'length' => 10, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => 'Unit to which it belongs', 'precision' => null, 'autoIncrement' => null],
		'_indexes' => [
			'FK_card_units_cards' => ['type' => 'index', 'columns' => ['card_id'], 'length' => []],
			'FK_card_units_units' => ['type' => 'index', 'columns' => ['unit_id'], 'length' => []],
		],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
			'FK_card_units_cards' => ['type' => 'foreign', 'columns' => ['card_id'], 'references' => ['cards', 'id'], 'update' => 'noAction', 'delete' => 'cascade', 'length' => []],
			'FK_card_units_units' => ['type' => 'foreign', 'columns' => ['unit_id'], 'references' => ['units', 'id'], 'update' => 'noAction', 'delete' => 'cascade', 'length' => []],
		],
		'_options' => [
			'engine' => 'InnoDB',
			'collation' => 'latin1_general_ci'
		],
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Init method
	 *
	 * @return void
	 */
	public function init(): void
	{
		$this->records = [
			[
				'id' => 3039,
				'card_id' => 7,
				'unit_id' => 134
			],
			[
				'id' => 3040,
				'card_id' => 4,
				'unit_id' => 134
			],
			[
				'id' => 3041,
				'card_id' => 8,
				'unit_id' => 134
			],
			[
				'id' => 3042,
				'card_id' => 3,
				'unit_id' => 134
			],
			[
				'id' => 3043,
				'card_id' => 5,
				'unit_id' => 134
			],
			[
				'id' => 3044,
				'card_id' => 9,
				'unit_id' => 134
			],
			[
				'id' => 3045,
				'card_id' => 813,
				'unit_id' => 134
			],
			[
				'id' => 3046,
				'card_id' => 10,
				'unit_id' => 134
			],
			[
				'id' => 3047,
				'card_id' => 4256,
				'unit_id' => 134
			],
			[
				'id' => 3048,
				'card_id' => 4257,
				'unit_id' => 134
			],
			[
				'id' => 3049,
				'card_id' => 4258,
				'unit_id' => 135
			],
			[
				'id' => 3050,
				'card_id' => 4269,
				'unit_id' => 135
			],
			[
				'id' => 3051,
				'card_id' => 4260,
				'unit_id' => 135
			],
			[
				'id' => 3052,
				'card_id' => 4270,
				'unit_id' => 135
			],
			[
				'id' => 3053,
				'card_id' => 4262,
				'unit_id' => 135
			],
			[
				'id' => 3054,
				'card_id' => 4272,
				'unit_id' => 135
			],
			[
				'id' => 3055,
				'card_id' => 4264,
				'unit_id' => 135
			],
			[
				'id' => 3056,
				'card_id' => 4274,
				'unit_id' => 135
			],
			[
				'id' => 3057,
				'card_id' => 4265,
				'unit_id' => 135
			],
			[
				'id' => 3058,
				'card_id' => 4275,
				'unit_id' => 135
			],
			[
				'id' => 3059,
				'card_id' => 4277,
				'unit_id' => 136
			],
			[
				'id' => 3060,
				'card_id' => 17,
				'unit_id' => 136
			],
			[
				'id' => 3061,
				'card_id' => 4259,
				'unit_id' => 136
			],
			[
				'id' => 3062,
				'card_id' => 15,
				'unit_id' => 136
			],
			[
				'id' => 3063,
				'card_id' => 4261,
				'unit_id' => 136
			],
			[
				'id' => 3064,
				'card_id' => 30,
				'unit_id' => 136
			],
			[
				'id' => 3065,
				'card_id' => 4263,
				'unit_id' => 136
			],
			[
				'id' => 3066,
				'card_id' => 4278,
				'unit_id' => 136
			],
			[
				'id' => 3067,
				'card_id' => 4266,
				'unit_id' => 136
			],
			[
				'id' => 3068,
				'card_id' => 4279,
				'unit_id' => 136
			],
			[
				'id' => 3069,
				'card_id' => 17,
				'unit_id' => 137
			],
			[
				'id' => 3070,
				'card_id' => 962,
				'unit_id' => 137
			],
			[
				'id' => 3071,
				'card_id' => 13,
				'unit_id' => 137
			],
			[
				'id' => 3072,
				'card_id' => 960,
				'unit_id' => 137
			],
			[
				'id' => 3073,
				'card_id' => 4278,
				'unit_id' => 137
			],
			[
				'id' => 3074,
				'card_id' => 4282,
				'unit_id' => 137
			],
			[
				'id' => 3075,
				'card_id' => 21,
				'unit_id' => 137
			],
			[
				'id' => 3076,
				'card_id' => 4280,
				'unit_id' => 137
			],
			[
				'id' => 3077,
				'card_id' => 4283,
				'unit_id' => 137
			],
			[
				'id' => 3078,
				'card_id' => 4281,
				'unit_id' => 137
			],
			[
				'id' => 3079,
				'card_id' => 15,
				'unit_id' => 137
			],
			[
				'id' => 3080,
				'card_id' => 16,
				'unit_id' => 137
			],
			[
				'id' => 3081,
				'card_id' => 19,
				'unit_id' => 137
			],
			[
				'id' => 3082,
				'card_id' => 20,
				'unit_id' => 137
			],
			[
				'id' => 3083,
				'card_id' => 11,
				'unit_id' => 137
			],
			[
				'id' => 3084,
				'card_id' => 12,
				'unit_id' => 137
			],
			[
				'id' => 3085,
				'card_id' => 23,
				'unit_id' => 137
			],
			[
				'id' => 3086,
				'card_id' => 4284,
				'unit_id' => 137
			],
			[
				'id' => 3087,
				'card_id' => 4279,
				'unit_id' => 137
			],
			[
				'id' => 3088,
				'card_id' => 4285,
				'unit_id' => 137
			],
			[
				'id' => 3109,
				'card_id' => 155,
				'unit_id' => 253
			],
			[
				'id' => 3110,
				'card_id' => 4304,
				'unit_id' => 253
			],
			[
				'id' => 3111,
				'card_id' => 4305,
				'unit_id' => 253
			],
			[
				'id' => 3112,
				'card_id' => 1068,
				'unit_id' => 253
			],
			[
				'id' => 3113,
				'card_id' => 177,
				'unit_id' => 253
			],
			[
				'id' => 3114,
				'card_id' => 4306,
				'unit_id' => 253
			],
			[
				'id' => 3115,
				'card_id' => 4307,
				'unit_id' => 253
			],
			[
				'id' => 3116,
				'card_id' => 174,
				'unit_id' => 253
			],
			[
				'id' => 3117,
				'card_id' => 4308,
				'unit_id' => 253
			],
			[
				'id' => 3118,
				'card_id' => 223,
				'unit_id' => 253
			],
			[
				'id' => 3119,
				'card_id' => 4347,
				'unit_id' => 253
			],
			[
				'id' => 3120,
				'card_id' => 247,
				'unit_id' => 253
			],
			[
				'id' => 3145,
				'card_id' => 775,
				'unit_id' => 255
			],
			[
				'id' => 3146,
				'card_id' => 66,
				'unit_id' => 255
			],
			[
				'id' => 3147,
				'card_id' => 53,
				'unit_id' => 255
			],
			[
				'id' => 3148,
				'card_id' => 57,
				'unit_id' => 255
			],
			[
				'id' => 3149,
				'card_id' => 170,
				'unit_id' => 255
			],
			[
				'id' => 3150,
				'card_id' => 65,
				'unit_id' => 255
			],
			[
				'id' => 3151,
				'card_id' => 60,
				'unit_id' => 255
			],
			[
				'id' => 3152,
				'card_id' => 56,
				'unit_id' => 255
			],
			[
				'id' => 3153,
				'card_id' => 165,
				'unit_id' => 255
			],
			[
				'id' => 3154,
				'card_id' => 166,
				'unit_id' => 255
			],
			[
				'id' => 3155,
				'card_id' => 168,
				'unit_id' => 255
			],
			[
				'id' => 3156,
				'card_id' => 52,
				'unit_id' => 255
			],
			[
				'id' => 3165,
				'card_id' => 332,
				'unit_id' => 178
			],
			[
				'id' => 3166,
				'card_id' => 333,
				'unit_id' => 178
			],
			[
				'id' => 3167,
				'card_id' => 334,
				'unit_id' => 178
			],
			[
				'id' => 3168,
				'card_id' => 335,
				'unit_id' => 178
			],
			[
				'id' => 3169,
				'card_id' => 336,
				'unit_id' => 178
			],
			[
				'id' => 3170,
				'card_id' => 337,
				'unit_id' => 178
			],
			[
				'id' => 3171,
				'card_id' => 338,
				'unit_id' => 178
			],
			[
				'id' => 3172,
				'card_id' => 339,
				'unit_id' => 179
			],
			[
				'id' => 3173,
				'card_id' => 340,
				'unit_id' => 179
			],
			[
				'id' => 3174,
				'card_id' => 341,
				'unit_id' => 179
			],
			[
				'id' => 3175,
				'card_id' => 269,
				'unit_id' => 179
			],
			[
				'id' => 3176,
				'card_id' => 343,
				'unit_id' => 179
			],
			[
				'id' => 3177,
				'card_id' => 344,
				'unit_id' => 179
			],
			[
				'id' => 3178,
				'card_id' => 345,
				'unit_id' => 179
			],
			[
				'id' => 3179,
				'card_id' => 346,
				'unit_id' => 179
			],
			[
				'id' => 3180,
				'card_id' => 347,
				'unit_id' => 180
			],
			[
				'id' => 3181,
				'card_id' => 348,
				'unit_id' => 180
			],
			[
				'id' => 3182,
				'card_id' => 349,
				'unit_id' => 180
			],
			[
				'id' => 3183,
				'card_id' => 350,
				'unit_id' => 180
			],
			[
				'id' => 3184,
				'card_id' => 4438,
				'unit_id' => 180
			],
			[
				'id' => 3185,
				'card_id' => 354,
				'unit_id' => 180
			],
			[
				'id' => 3186,
				'card_id' => 379,
				'unit_id' => 180
			],
			[
				'id' => 3187,
				'card_id' => 359,
				'unit_id' => 180
			],
			[
				'id' => 3188,
				'card_id' => 360,
				'unit_id' => 180
			],
			[
				'id' => 3189,
				'card_id' => 351,
				'unit_id' => 180
			],
			[
				'id' => 3190,
				'card_id' => 361,
				'unit_id' => 180
			],
			[
				'id' => 3191,
				'card_id' => 362,
				'unit_id' => 180
			],
			[
				'id' => 3192,
				'card_id' => 368,
				'unit_id' => 180
			],
			[
				'id' => 3193,
				'card_id' => 363,
				'unit_id' => 180
			],
			[
				'id' => 3194,
				'card_id' => 353,
				'unit_id' => 180
			],
			[
				'id' => 3195,
				'card_id' => 371,
				'unit_id' => 180
			],
			[
				'id' => 3196,
				'card_id' => 356,
				'unit_id' => 180
			],
			[
				'id' => 3197,
				'card_id' => 373,
				'unit_id' => 180
			],
			[
				'id' => 3198,
				'card_id' => 374,
				'unit_id' => 180
			],
			[
				'id' => 3199,
				'card_id' => 375,
				'unit_id' => 180
			],
			[
				'id' => 3200,
				'card_id' => 382,
				'unit_id' => 181
			],
			[
				'id' => 3201,
				'card_id' => 428,
				'unit_id' => 181
			],
			[
				'id' => 3202,
				'card_id' => 426,
				'unit_id' => 181
			],
			[
				'id' => 3203,
				'card_id' => 415,
				'unit_id' => 181
			],
			[
				'id' => 3204,
				'card_id' => 777,
				'unit_id' => 181
			],
			[
				'id' => 3205,
				'card_id' => 404,
				'unit_id' => 181
			],
			[
				'id' => 3206,
				'card_id' => 473,
				'unit_id' => 181
			],
			[
				'id' => 3207,
				'card_id' => 165,
				'unit_id' => 181
			],
			[
				'id' => 3208,
				'card_id' => 405,
				'unit_id' => 181
			],
			[
				'id' => 3209,
				'card_id' => 461,
				'unit_id' => 181
			],
			[
				'id' => 3210,
				'card_id' => 7,
				'unit_id' => 181
			],
			[
				'id' => 3211,
				'card_id' => 480,
				'unit_id' => 181
			],
			[
				'id' => 3212,
				'card_id' => 489,
				'unit_id' => 182
			],
			[
				'id' => 3213,
				'card_id' => 490,
				'unit_id' => 182
			],
			[
				'id' => 3214,
				'card_id' => 491,
				'unit_id' => 182
			],
			[
				'id' => 3215,
				'card_id' => 492,
				'unit_id' => 182
			],
			[
				'id' => 3216,
				'card_id' => 4437,
				'unit_id' => 182
			],
			[
				'id' => 3217,
				'card_id' => 501,
				'unit_id' => 182
			],
			[
				'id' => 3218,
				'card_id' => 502,
				'unit_id' => 182
			],
			[
				'id' => 3219,
				'card_id' => 510,
				'unit_id' => 182
			],
			[
				'id' => 3220,
				'card_id' => 511,
				'unit_id' => 182
			],
			[
				'id' => 3221,
				'card_id' => 517,
				'unit_id' => 182
			],
			[
				'id' => 3222,
				'card_id' => 499,
				'unit_id' => 182
			],
			[
				'id' => 3223,
				'card_id' => 508,
				'unit_id' => 182
			],
			[
				'id' => 3224,
				'card_id' => 493,
				'unit_id' => 182
			],
			[
				'id' => 3225,
				'card_id' => 509,
				'unit_id' => 182
			],
			[
				'id' => 3226,
				'card_id' => 523,
				'unit_id' => 183
			],
			[
				'id' => 3227,
				'card_id' => 205,
				'unit_id' => 183
			],
			[
				'id' => 3228,
				'card_id' => 526,
				'unit_id' => 183
			],
			[
				'id' => 3229,
				'card_id' => 525,
				'unit_id' => 183
			],
			[
				'id' => 3230,
				'card_id' => 484,
				'unit_id' => 183
			],
			[
				'id' => 3231,
				'card_id' => 528,
				'unit_id' => 183
			],
			[
				'id' => 3232,
				'card_id' => 529,
				'unit_id' => 183
			],
			[
				'id' => 3233,
				'card_id' => 530,
				'unit_id' => 183
			],
			[
				'id' => 3234,
				'card_id' => 531,
				'unit_id' => 183
			],
			[
				'id' => 3235,
				'card_id' => 532,
				'unit_id' => 183
			],
			[
				'id' => 3236,
				'card_id' => 596,
				'unit_id' => 183
			],
			[
				'id' => 3237,
				'card_id' => 597,
				'unit_id' => 183
			],
			[
				'id' => 3238,
				'card_id' => 575,
				'unit_id' => 183
			],
			[
				'id' => 3239,
				'card_id' => 576,
				'unit_id' => 183
			],
			[
				'id' => 3240,
				'card_id' => 579,
				'unit_id' => 183
			],
			[
				'id' => 3241,
				'card_id' => 581,
				'unit_id' => 183
			],
			[
				'id' => 3242,
				'card_id' => 582,
				'unit_id' => 183
			],
			[
				'id' => 3243,
				'card_id' => 584,
				'unit_id' => 183
			],
			[
				'id' => 3256,
				'card_id' => 233,
				'unit_id' => 185
			],
			[
				'id' => 3257,
				'card_id' => 628,
				'unit_id' => 185
			],
			[
				'id' => 3258,
				'card_id' => 554,
				'unit_id' => 185
			],
			[
				'id' => 3259,
				'card_id' => 630,
				'unit_id' => 185
			],
			[
				'id' => 3260,
				'card_id' => 631,
				'unit_id' => 185
			],
			[
				'id' => 3261,
				'card_id' => 558,
				'unit_id' => 185
			],
			[
				'id' => 3262,
				'card_id' => 549,
				'unit_id' => 185
			],
			[
				'id' => 3263,
				'card_id' => 634,
				'unit_id' => 185
			],
			[
				'id' => 3264,
				'card_id' => 635,
				'unit_id' => 185
			],
			[
				'id' => 3265,
				'card_id' => 636,
				'unit_id' => 185
			],
			[
				'id' => 3266,
				'card_id' => 637,
				'unit_id' => 185
			],
			[
				'id' => 3267,
				'card_id' => 205,
				'unit_id' => 186
			],
			[
				'id' => 3268,
				'card_id' => 530,
				'unit_id' => 186
			],
			[
				'id' => 3269,
				'card_id' => 640,
				'unit_id' => 186
			],
			[
				'id' => 3270,
				'card_id' => 641,
				'unit_id' => 186
			],
			[
				'id' => 3271,
				'card_id' => 642,
				'unit_id' => 186
			],
			[
				'id' => 3272,
				'card_id' => 656,
				'unit_id' => 186
			],
			[
				'id' => 3273,
				'card_id' => 657,
				'unit_id' => 186
			],
			[
				'id' => 3274,
				'card_id' => 668,
				'unit_id' => 186
			],
			[
				'id' => 3275,
				'card_id' => 669,
				'unit_id' => 186
			],
			[
				'id' => 3276,
				'card_id' => 667,
				'unit_id' => 186
			],
			[
				'id' => 3277,
				'card_id' => 646,
				'unit_id' => 186
			],
			[
				'id' => 3278,
				'card_id' => 644,
				'unit_id' => 186
			],
			[
				'id' => 3279,
				'card_id' => 675,
				'unit_id' => 186
			],
			[
				'id' => 3280,
				'card_id' => 673,
				'unit_id' => 186
			],
			[
				'id' => 3281,
				'card_id' => 665,
				'unit_id' => 186
			],
			[
				'id' => 3282,
				'card_id' => 688,
				'unit_id' => 187
			],
			[
				'id' => 3283,
				'card_id' => 383,
				'unit_id' => 187
			],
			[
				'id' => 3284,
				'card_id' => 692,
				'unit_id' => 187
			],
			[
				'id' => 3285,
				'card_id' => 678,
				'unit_id' => 187
			],
			[
				'id' => 3286,
				'card_id' => 687,
				'unit_id' => 187
			],
			[
				'id' => 3287,
				'card_id' => 681,
				'unit_id' => 187
			],
			[
				'id' => 3288,
				'card_id' => 690,
				'unit_id' => 187
			],
			[
				'id' => 3289,
				'card_id' => 187,
				'unit_id' => 187
			],
			[
				'id' => 3290,
				'card_id' => 691,
				'unit_id' => 187
			],
			[
				'id' => 3291,
				'card_id' => 679,
				'unit_id' => 187
			],
			[
				'id' => 3292,
				'card_id' => 701,
				'unit_id' => 188
			],
			[
				'id' => 3293,
				'card_id' => 693,
				'unit_id' => 188
			],
			[
				'id' => 3294,
				'card_id' => 697,
				'unit_id' => 188
			],
			[
				'id' => 3295,
				'card_id' => 702,
				'unit_id' => 188
			],
			[
				'id' => 3296,
				'card_id' => 710,
				'unit_id' => 188
			],
			[
				'id' => 3297,
				'card_id' => 706,
				'unit_id' => 188
			],
			[
				'id' => 3298,
				'card_id' => 441,
				'unit_id' => 189
			],
			[
				'id' => 3299,
				'card_id' => 348,
				'unit_id' => 189
			],
			[
				'id' => 3300,
				'card_id' => 733,
				'unit_id' => 189
			],
			[
				'id' => 3301,
				'card_id' => 442,
				'unit_id' => 189
			],
			[
				'id' => 3302,
				'card_id' => 738,
				'unit_id' => 189
			],
			[
				'id' => 3303,
				'card_id' => 740,
				'unit_id' => 189
			],
			[
				'id' => 3304,
				'card_id' => 741,
				'unit_id' => 189
			],
			[
				'id' => 3305,
				'card_id' => 743,
				'unit_id' => 189
			],
			[
				'id' => 3306,
				'card_id' => 764,
				'unit_id' => 190
			],
			[
				'id' => 3307,
				'card_id' => 748,
				'unit_id' => 190
			],
			[
				'id' => 3308,
				'card_id' => 697,
				'unit_id' => 190
			],
			[
				'id' => 3309,
				'card_id' => 745,
				'unit_id' => 190
			],
			[
				'id' => 3310,
				'card_id' => 752,
				'unit_id' => 190
			],
			[
				'id' => 3311,
				'card_id' => 767,
				'unit_id' => 190
			],
			[
				'id' => 3312,
				'card_id' => 760,
				'unit_id' => 190
			],
			[
				'id' => 3313,
				'card_id' => 763,
				'unit_id' => 190
			],
			[
				'id' => 3314,
				'card_id' => 704,
				'unit_id' => 190
			],
			[
				'id' => 3315,
				'card_id' => 296,
				'unit_id' => 190
			],
			[
				'id' => 3316,
				'card_id' => 436,
				'unit_id' => 190
			],
			[
				'id' => 3317,
				'card_id' => 777,
				'unit_id' => 190
			],
			[
				'id' => 3318,
				'card_id' => 63,
				'unit_id' => 190
			],
			[
				'id' => 3319,
				'card_id' => 66,
				'unit_id' => 190
			],
			[
				'id' => 3320,
				'card_id' => 166,
				'unit_id' => 190
			],
			[
				'id' => 3321,
				'card_id' => 225,
				'unit_id' => 190
			],
			[
				'id' => 3322,
				'card_id' => 8,
				'unit_id' => 191
			],
			[
				'id' => 3323,
				'card_id' => 784,
				'unit_id' => 191
			],
			[
				'id' => 3324,
				'card_id' => 5,
				'unit_id' => 191
			],
			[
				'id' => 3325,
				'card_id' => 786,
				'unit_id' => 191
			],
			[
				'id' => 3326,
				'card_id' => 7,
				'unit_id' => 191
			],
			[
				'id' => 3327,
				'card_id' => 788,
				'unit_id' => 191
			],
			[
				'id' => 3328,
				'card_id' => 3,
				'unit_id' => 191
			],
			[
				'id' => 3329,
				'card_id' => 790,
				'unit_id' => 191
			],
			[
				'id' => 3330,
				'card_id' => 4498,
				'unit_id' => 191
			],
			[
				'id' => 3331,
				'card_id' => 792,
				'unit_id' => 191
			],
			[
				'id' => 3332,
				'card_id' => 9,
				'unit_id' => 191
			],
			[
				'id' => 3333,
				'card_id' => 794,
				'unit_id' => 191
			],
			[
				'id' => 3334,
				'card_id' => 168,
				'unit_id' => 191
			],
			[
				'id' => 3335,
				'card_id' => 796,
				'unit_id' => 191
			],
			[
				'id' => 3336,
				'card_id' => 545,
				'unit_id' => 191
			],
			[
				'id' => 3337,
				'card_id' => 798,
				'unit_id' => 191
			],
			[
				'id' => 3338,
				'card_id' => 63,
				'unit_id' => 191
			],
			[
				'id' => 3339,
				'card_id' => 800,
				'unit_id' => 191
			],
			[
				'id' => 3340,
				'card_id' => 66,
				'unit_id' => 191
			],
			[
				'id' => 3341,
				'card_id' => 802,
				'unit_id' => 191
			],
			[
				'id' => 3342,
				'card_id' => 60,
				'unit_id' => 191
			],
			[
				'id' => 3343,
				'card_id' => 804,
				'unit_id' => 191
			],
			[
				'id' => 3344,
				'card_id' => 56,
				'unit_id' => 191
			],
			[
				'id' => 3345,
				'card_id' => 806,
				'unit_id' => 191
			],
			[
				'id' => 3346,
				'card_id' => 65,
				'unit_id' => 191
			],
			[
				'id' => 3347,
				'card_id' => 808,
				'unit_id' => 191
			],
			[
				'id' => 3348,
				'card_id' => 52,
				'unit_id' => 191
			],
			[
				'id' => 3349,
				'card_id' => 810,
				'unit_id' => 191
			],
			[
				'id' => 3350,
				'card_id' => 279,
				'unit_id' => 191
			],
			[
				'id' => 3351,
				'card_id' => 812,
				'unit_id' => 191
			],
			[
				'id' => 3352,
				'card_id' => 4499,
				'unit_id' => 191
			],
			[
				'id' => 3353,
				'card_id' => 814,
				'unit_id' => 191
			],
			[
				'id' => 3354,
				'card_id' => 4500,
				'unit_id' => 191
			],
			[
				'id' => 3355,
				'card_id' => 816,
				'unit_id' => 191
			],
			[
				'id' => 3356,
				'card_id' => 781,
				'unit_id' => 191
			],
			[
				'id' => 3357,
				'card_id' => 782,
				'unit_id' => 191
			],
			[
				'id' => 3358,
				'card_id' => 817,
				'unit_id' => 191
			],
			[
				'id' => 3359,
				'card_id' => 818,
				'unit_id' => 191
			],
			[
				'id' => 3360,
				'card_id' => 749,
				'unit_id' => 191
			],
			[
				'id' => 3361,
				'card_id' => 772,
				'unit_id' => 191
			],
			[
				'id' => 3362,
				'card_id' => 821,
				'unit_id' => 191
			],
			[
				'id' => 3363,
				'card_id' => 748,
				'unit_id' => 191
			],
			[
				'id' => 3364,
				'card_id' => 757,
				'unit_id' => 191
			],
			[
				'id' => 3365,
				'card_id' => 752,
				'unit_id' => 191
			],
			[
				'id' => 3366,
				'card_id' => 763,
				'unit_id' => 191
			],
			[
				'id' => 3367,
				'card_id' => 791,
				'unit_id' => 191
			],
			[
				'id' => 3368,
				'card_id' => 797,
				'unit_id' => 191
			],
			[
				'id' => 3369,
				'card_id' => 775,
				'unit_id' => 191
			],
			[
				'id' => 3370,
				'card_id' => 813,
				'unit_id' => 191
			],
			[
				'id' => 3371,
				'card_id' => 815,
				'unit_id' => 191
			],
			[
				'id' => 3372,
				'card_id' => 755,
				'unit_id' => 192
			],
			[
				'id' => 3373,
				'card_id' => 764,
				'unit_id' => 192
			],
			[
				'id' => 3374,
				'card_id' => 828,
				'unit_id' => 192
			],
			[
				'id' => 3375,
				'card_id' => 829,
				'unit_id' => 192
			],
			[
				'id' => 3376,
				'card_id' => 830,
				'unit_id' => 192
			],
			[
				'id' => 3377,
				'card_id' => 771,
				'unit_id' => 192
			],
			[
				'id' => 3378,
				'card_id' => 836,
				'unit_id' => 192
			],
			[
				'id' => 3379,
				'card_id' => 833,
				'unit_id' => 192
			],
			[
				'id' => 3380,
				'card_id' => 28,
				'unit_id' => 193
			],
			[
				'id' => 3381,
				'card_id' => 850,
				'unit_id' => 193
			],
			[
				'id' => 3382,
				'card_id' => 4082,
				'unit_id' => 193
			],
			[
				'id' => 3383,
				'card_id' => 852,
				'unit_id' => 193
			],
			[
				'id' => 3384,
				'card_id' => 838,
				'unit_id' => 193
			],
			[
				'id' => 3385,
				'card_id' => 840,
				'unit_id' => 193
			],
			[
				'id' => 3386,
				'card_id' => 842,
				'unit_id' => 193
			],
			[
				'id' => 3387,
				'card_id' => 843,
				'unit_id' => 193
			],
			[
				'id' => 3388,
				'card_id' => 170,
				'unit_id' => 193
			],
			[
				'id' => 3389,
				'card_id' => 845,
				'unit_id' => 193
			],
			[
				'id' => 3390,
				'card_id' => 846,
				'unit_id' => 193
			],
			[
				'id' => 3391,
				'card_id' => 847,
				'unit_id' => 193
			],
			[
				'id' => 3392,
				'card_id' => 848,
				'unit_id' => 193
			],
			[
				'id' => 3393,
				'card_id' => 4077,
				'unit_id' => 193
			],
			[
				'id' => 3394,
				'card_id' => 870,
				'unit_id' => 193
			],
			[
				'id' => 3395,
				'card_id' => 41,
				'unit_id' => 193
			],
			[
				'id' => 3396,
				'card_id' => 854,
				'unit_id' => 193
			],
			[
				'id' => 3397,
				'card_id' => 856,
				'unit_id' => 193
			],
			[
				'id' => 3398,
				'card_id' => 857,
				'unit_id' => 193
			],
			[
				'id' => 3399,
				'card_id' => 860,
				'unit_id' => 193
			],
			[
				'id' => 3400,
				'card_id' => 862,
				'unit_id' => 193
			],
			[
				'id' => 3401,
				'card_id' => 864,
				'unit_id' => 193
			],
			[
				'id' => 3402,
				'card_id' => 865,
				'unit_id' => 193
			],
			[
				'id' => 3403,
				'card_id' => 868,
				'unit_id' => 193
			],
			[
				'id' => 3404,
				'card_id' => 4082,
				'unit_id' => 194
			],
			[
				'id' => 3405,
				'card_id' => 872,
				'unit_id' => 194
			],
			[
				'id' => 3406,
				'card_id' => 853,
				'unit_id' => 194
			],
			[
				'id' => 3407,
				'card_id' => 874,
				'unit_id' => 194
			],
			[
				'id' => 3408,
				'card_id' => 890,
				'unit_id' => 194
			],
			[
				'id' => 3409,
				'card_id' => 865,
				'unit_id' => 194
			],
			[
				'id' => 3410,
				'card_id' => 869,
				'unit_id' => 194
			],
			[
				'id' => 3411,
				'card_id' => 990,
				'unit_id' => 194
			],
			[
				'id' => 3412,
				'card_id' => 876,
				'unit_id' => 194
			],
			[
				'id' => 3413,
				'card_id' => 859,
				'unit_id' => 194
			],
			[
				'id' => 3414,
				'card_id' => 888,
				'unit_id' => 194
			],
			[
				'id' => 3415,
				'card_id' => 882,
				'unit_id' => 194
			],
			[
				'id' => 3416,
				'card_id' => 1211,
				'unit_id' => 194
			],
			[
				'id' => 3417,
				'card_id' => 884,
				'unit_id' => 194
			],
			[
				'id' => 3418,
				'card_id' => 867,
				'unit_id' => 194
			],
			[
				'id' => 3419,
				'card_id' => 878,
				'unit_id' => 194
			],
			[
				'id' => 3420,
				'card_id' => 4082,
				'unit_id' => 195
			],
			[
				'id' => 3421,
				'card_id' => 892,
				'unit_id' => 195
			],
			[
				'id' => 3422,
				'card_id' => 853,
				'unit_id' => 195
			],
			[
				'id' => 3423,
				'card_id' => 894,
				'unit_id' => 195
			],
			[
				'id' => 3424,
				'card_id' => 898,
				'unit_id' => 195
			],
			[
				'id' => 3425,
				'card_id' => 886,
				'unit_id' => 195
			],
			[
				'id' => 3426,
				'card_id' => 857,
				'unit_id' => 195
			],
			[
				'id' => 3427,
				'card_id' => 911,
				'unit_id' => 195
			],
			[
				'id' => 3428,
				'card_id' => 863,
				'unit_id' => 195
			],
			[
				'id' => 3429,
				'card_id' => 900,
				'unit_id' => 195
			],
			[
				'id' => 3430,
				'card_id' => 4084,
				'unit_id' => 195
			],
			[
				'id' => 3431,
				'card_id' => 902,
				'unit_id' => 195
			],
			[
				'id' => 3432,
				'card_id' => 4085,
				'unit_id' => 195
			],
			[
				'id' => 3433,
				'card_id' => 906,
				'unit_id' => 195
			],
			[
				'id' => 3434,
				'card_id' => 908,
				'unit_id' => 195
			],
			[
				'id' => 3435,
				'card_id' => 861,
				'unit_id' => 195
			],
			[
				'id' => 3436,
				'card_id' => 913,
				'unit_id' => 196
			],
			[
				'id' => 3437,
				'card_id' => 914,
				'unit_id' => 196
			],
			[
				'id' => 3438,
				'card_id' => 915,
				'unit_id' => 196
			],
			[
				'id' => 3439,
				'card_id' => 916,
				'unit_id' => 196
			],
			[
				'id' => 3440,
				'card_id' => 53,
				'unit_id' => 196
			],
			[
				'id' => 3441,
				'card_id' => 917,
				'unit_id' => 196
			],
			[
				'id' => 3442,
				'card_id' => 918,
				'unit_id' => 196
			],
			[
				'id' => 3443,
				'card_id' => 545,
				'unit_id' => 196
			],
			[
				'id' => 3444,
				'card_id' => 920,
				'unit_id' => 196
			],
			[
				'id' => 3445,
				'card_id' => 921,
				'unit_id' => 196
			],
			[
				'id' => 3446,
				'card_id' => 922,
				'unit_id' => 196
			],
			[
				'id' => 3447,
				'card_id' => 912,
				'unit_id' => 196
			],
			[
				'id' => 3448,
				'card_id' => 938,
				'unit_id' => 196
			],
			[
				'id' => 3449,
				'card_id' => 927,
				'unit_id' => 196
			],
			[
				'id' => 3450,
				'card_id' => 939,
				'unit_id' => 196
			],
			[
				'id' => 3451,
				'card_id' => 924,
				'unit_id' => 196
			],
			[
				'id' => 3452,
				'card_id' => 60,
				'unit_id' => 196
			],
			[
				'id' => 3453,
				'card_id' => 8,
				'unit_id' => 196
			],
			[
				'id' => 3454,
				'card_id' => 7,
				'unit_id' => 196
			],
			[
				'id' => 3455,
				'card_id' => 56,
				'unit_id' => 196
			],
			[
				'id' => 3456,
				'card_id' => 65,
				'unit_id' => 196
			],
			[
				'id' => 3457,
				'card_id' => 279,
				'unit_id' => 196
			],
			[
				'id' => 3458,
				'card_id' => 951,
				'unit_id' => 196
			],
			[
				'id' => 3459,
				'card_id' => 551,
				'unit_id' => 196
			],
			[
				'id' => 3460,
				'card_id' => 953,
				'unit_id' => 196
			],
			[
				'id' => 3461,
				'card_id' => 954,
				'unit_id' => 196
			],
			[
				'id' => 3462,
				'card_id' => 955,
				'unit_id' => 196
			],
			[
				'id' => 3463,
				'card_id' => 37,
				'unit_id' => 196
			],
			[
				'id' => 3464,
				'card_id' => 958,
				'unit_id' => 196
			],
			[
				'id' => 3465,
				'card_id' => 26,
				'unit_id' => 196
			],
			[
				'id' => 3466,
				'card_id' => 960,
				'unit_id' => 196
			],
			[
				'id' => 3467,
				'card_id' => 17,
				'unit_id' => 196
			],
			[
				'id' => 3468,
				'card_id' => 962,
				'unit_id' => 196
			],
			[
				'id' => 3469,
				'card_id' => 39,
				'unit_id' => 196
			],
			[
				'id' => 3470,
				'card_id' => 40,
				'unit_id' => 196
			],
			[
				'id' => 3471,
				'card_id' => 934,
				'unit_id' => 196
			],
			[
				'id' => 3472,
				'card_id' => 944,
				'unit_id' => 196
			],
			[
				'id' => 3473,
				'card_id' => 926,
				'unit_id' => 196
			],
			[
				'id' => 3474,
				'card_id' => 33,
				'unit_id' => 196
			],
			[
				'id' => 3475,
				'card_id' => 968,
				'unit_id' => 196
			],
			[
				'id' => 3476,
				'card_id' => 932,
				'unit_id' => 196
			],
			[
				'id' => 3477,
				'card_id' => 956,
				'unit_id' => 196
			],
			[
				'id' => 3478,
				'card_id' => 930,
				'unit_id' => 196
			],
			[
				'id' => 3479,
				'card_id' => 45,
				'unit_id' => 196
			],
			[
				'id' => 3480,
				'card_id' => 972,
				'unit_id' => 196
			],
			[
				'id' => 3481,
				'card_id' => 974,
				'unit_id' => 196
			],
			[
				'id' => 3482,
				'card_id' => 936,
				'unit_id' => 196
			],
			[
				'id' => 3483,
				'card_id' => 975,
				'unit_id' => 197
			],
			[
				'id' => 3484,
				'card_id' => 4086,
				'unit_id' => 197
			],
			[
				'id' => 3485,
				'card_id' => 4087,
				'unit_id' => 197
			],
			[
				'id' => 3486,
				'card_id' => 4088,
				'unit_id' => 197
			],
			[
				'id' => 3487,
				'card_id' => 4090,
				'unit_id' => 197
			],
			[
				'id' => 3488,
				'card_id' => 4091,
				'unit_id' => 197
			],
			[
				'id' => 3489,
				'card_id' => 4092,
				'unit_id' => 197
			],
			[
				'id' => 3490,
				'card_id' => 4093,
				'unit_id' => 197
			],
			[
				'id' => 3491,
				'card_id' => 4094,
				'unit_id' => 197
			],
			[
				'id' => 3492,
				'card_id' => 4095,
				'unit_id' => 197
			],
			[
				'id' => 3493,
				'card_id' => 4089,
				'unit_id' => 197
			],
			[
				'id' => 3494,
				'card_id' => 1007,
				'unit_id' => 198
			],
			[
				'id' => 3495,
				'card_id' => 999,
				'unit_id' => 198
			],
			[
				'id' => 3496,
				'card_id' => 1000,
				'unit_id' => 198
			],
			[
				'id' => 3497,
				'card_id' => 1004,
				'unit_id' => 198
			],
			[
				'id' => 3498,
				'card_id' => 1015,
				'unit_id' => 198
			],
			[
				'id' => 3499,
				'card_id' => 1012,
				'unit_id' => 198
			],
			[
				'id' => 3500,
				'card_id' => 978,
				'unit_id' => 199
			],
			[
				'id' => 3501,
				'card_id' => 1019,
				'unit_id' => 199
			],
			[
				'id' => 3502,
				'card_id' => 975,
				'unit_id' => 199
			],
			[
				'id' => 3503,
				'card_id' => 1017,
				'unit_id' => 199
			],
			[
				'id' => 3504,
				'card_id' => 1022,
				'unit_id' => 199
			],
			[
				'id' => 3505,
				'card_id' => 1023,
				'unit_id' => 199
			],
			[
				'id' => 3506,
				'card_id' => 1024,
				'unit_id' => 199
			],
			[
				'id' => 3507,
				'card_id' => 1025,
				'unit_id' => 199
			],
			[
				'id' => 3508,
				'card_id' => 1020,
				'unit_id' => 199
			],
			[
				'id' => 3509,
				'card_id' => 1021,
				'unit_id' => 199
			],
			[
				'id' => 3510,
				'card_id' => 981,
				'unit_id' => 199
			],
			[
				'id' => 3511,
				'card_id' => 1029,
				'unit_id' => 199
			],
			[
				'id' => 3512,
				'card_id' => 990,
				'unit_id' => 199
			],
			[
				'id' => 3513,
				'card_id' => 1031,
				'unit_id' => 199
			],
			[
				'id' => 3514,
				'card_id' => 996,
				'unit_id' => 199
			],
			[
				'id' => 3515,
				'card_id' => 1027,
				'unit_id' => 199
			],
			[
				'id' => 3516,
				'card_id' => 984,
				'unit_id' => 199
			],
			[
				'id' => 3517,
				'card_id' => 1033,
				'unit_id' => 199
			],
			[
				'id' => 3518,
				'card_id' => 1034,
				'unit_id' => 199
			],
			[
				'id' => 3519,
				'card_id' => 1035,
				'unit_id' => 199
			],
			[
				'id' => 3520,
				'card_id' => 4097,
				'unit_id' => 199
			],
			[
				'id' => 3521,
				'card_id' => 4098,
				'unit_id' => 199
			],
			[
				'id' => 3522,
				'card_id' => 4099,
				'unit_id' => 199
			],
			[
				'id' => 3523,
				'card_id' => 4101,
				'unit_id' => 199
			],
			[
				'id' => 3524,
				'card_id' => 4102,
				'unit_id' => 199
			],
			[
				'id' => 3525,
				'card_id' => 4103,
				'unit_id' => 199
			],
			[
				'id' => 3526,
				'card_id' => 4104,
				'unit_id' => 199
			],
			[
				'id' => 3527,
				'card_id' => 1037,
				'unit_id' => 200
			],
			[
				'id' => 3528,
				'card_id' => 1039,
				'unit_id' => 200
			],
			[
				'id' => 3529,
				'card_id' => 1006,
				'unit_id' => 200
			],
			[
				'id' => 3530,
				'card_id' => 1043,
				'unit_id' => 200
			],
			[
				'id' => 3531,
				'card_id' => 1011,
				'unit_id' => 200
			],
			[
				'id' => 3532,
				'card_id' => 1047,
				'unit_id' => 200
			],
			[
				'id' => 3533,
				'card_id' => 1048,
				'unit_id' => 201
			],
			[
				'id' => 3534,
				'card_id' => 1049,
				'unit_id' => 201
			],
			[
				'id' => 3535,
				'card_id' => 1050,
				'unit_id' => 201
			],
			[
				'id' => 3536,
				'card_id' => 1051,
				'unit_id' => 201
			],
			[
				'id' => 3537,
				'card_id' => 1052,
				'unit_id' => 201
			],
			[
				'id' => 3538,
				'card_id' => 1053,
				'unit_id' => 201
			],
			[
				'id' => 3539,
				'card_id' => 1054,
				'unit_id' => 201
			],
			[
				'id' => 3540,
				'card_id' => 1055,
				'unit_id' => 201
			],
			[
				'id' => 3541,
				'card_id' => 1056,
				'unit_id' => 201
			],
			[
				'id' => 3542,
				'card_id' => 1057,
				'unit_id' => 201
			],
			[
				'id' => 3543,
				'card_id' => 1058,
				'unit_id' => 201
			],
			[
				'id' => 3544,
				'card_id' => 1059,
				'unit_id' => 201
			],
			[
				'id' => 3545,
				'card_id' => 4109,
				'unit_id' => 202
			],
			[
				'id' => 3546,
				'card_id' => 1068,
				'unit_id' => 202
			],
			[
				'id' => 3547,
				'card_id' => 1065,
				'unit_id' => 202
			],
			[
				'id' => 3548,
				'card_id' => 4106,
				'unit_id' => 202
			],
			[
				'id' => 3549,
				'card_id' => 1060,
				'unit_id' => 202
			],
			[
				'id' => 3550,
				'card_id' => 1076,
				'unit_id' => 202
			],
			[
				'id' => 3551,
				'card_id' => 254,
				'unit_id' => 202
			],
			[
				'id' => 3552,
				'card_id' => 4110,
				'unit_id' => 202
			],
			[
				'id' => 3559,
				'card_id' => 1083,
				'unit_id' => 204
			],
			[
				'id' => 3560,
				'card_id' => 1069,
				'unit_id' => 204
			],
			[
				'id' => 3561,
				'card_id' => 1085,
				'unit_id' => 204
			],
			[
				'id' => 3562,
				'card_id' => 1086,
				'unit_id' => 204
			],
			[
				'id' => 3563,
				'card_id' => 1087,
				'unit_id' => 204
			],
			[
				'id' => 3564,
				'card_id' => 1088,
				'unit_id' => 204
			],
			[
				'id' => 3565,
				'card_id' => 175,
				'unit_id' => 204
			],
			[
				'id' => 3566,
				'card_id' => 243,
				'unit_id' => 204
			],
			[
				'id' => 3567,
				'card_id' => 1091,
				'unit_id' => 204
			],
			[
				'id' => 3568,
				'card_id' => 391,
				'unit_id' => 204
			],
			[
				'id' => 3569,
				'card_id' => 1093,
				'unit_id' => 205
			],
			[
				'id' => 3570,
				'card_id' => 1095,
				'unit_id' => 205
			],
			[
				'id' => 3571,
				'card_id' => 1098,
				'unit_id' => 205
			],
			[
				'id' => 3572,
				'card_id' => 1100,
				'unit_id' => 205
			],
			[
				'id' => 3573,
				'card_id' => 1102,
				'unit_id' => 205
			],
			[
				'id' => 3574,
				'card_id' => 1103,
				'unit_id' => 205
			],
			[
				'id' => 3575,
				'card_id' => 1106,
				'unit_id' => 205
			],
			[
				'id' => 3576,
				'card_id' => 1107,
				'unit_id' => 205
			],
			[
				'id' => 3577,
				'card_id' => 1109,
				'unit_id' => 205
			],
			[
				'id' => 3578,
				'card_id' => 1112,
				'unit_id' => 205
			],
			[
				'id' => 3579,
				'card_id' => 1113,
				'unit_id' => 206
			],
			[
				'id' => 3580,
				'card_id' => 1114,
				'unit_id' => 206
			],
			[
				'id' => 3581,
				'card_id' => 1115,
				'unit_id' => 206
			],
			[
				'id' => 3582,
				'card_id' => 1116,
				'unit_id' => 206
			],
			[
				'id' => 3583,
				'card_id' => 1117,
				'unit_id' => 206
			],
			[
				'id' => 3584,
				'card_id' => 1118,
				'unit_id' => 206
			],
			[
				'id' => 3585,
				'card_id' => 1119,
				'unit_id' => 206
			],
			[
				'id' => 3586,
				'card_id' => 1120,
				'unit_id' => 206
			],
			[
				'id' => 3587,
				'card_id' => 1121,
				'unit_id' => 206
			],
			[
				'id' => 3588,
				'card_id' => 1122,
				'unit_id' => 206
			],
			[
				'id' => 3589,
				'card_id' => 1123,
				'unit_id' => 206
			],
			[
				'id' => 3590,
				'card_id' => 1124,
				'unit_id' => 206
			],
			[
				'id' => 3591,
				'card_id' => 1125,
				'unit_id' => 206
			],
			[
				'id' => 3592,
				'card_id' => 1126,
				'unit_id' => 206
			],
			[
				'id' => 3593,
				'card_id' => 1127,
				'unit_id' => 206
			],
			[
				'id' => 3594,
				'card_id' => 1128,
				'unit_id' => 206
			],
			[
				'id' => 3595,
				'card_id' => 1129,
				'unit_id' => 206
			],
			[
				'id' => 3596,
				'card_id' => 1130,
				'unit_id' => 206
			],
			[
				'id' => 3597,
				'card_id' => 1131,
				'unit_id' => 206
			],
			[
				'id' => 3598,
				'card_id' => 1132,
				'unit_id' => 206
			],
			[
				'id' => 3599,
				'card_id' => 1133,
				'unit_id' => 207
			],
			[
				'id' => 3600,
				'card_id' => 1134,
				'unit_id' => 207
			],
			[
				'id' => 3601,
				'card_id' => 1135,
				'unit_id' => 207
			],
			[
				'id' => 3602,
				'card_id' => 1136,
				'unit_id' => 207
			],
			[
				'id' => 3603,
				'card_id' => 1117,
				'unit_id' => 207
			],
			[
				'id' => 3604,
				'card_id' => 1138,
				'unit_id' => 207
			],
			[
				'id' => 3605,
				'card_id' => 1119,
				'unit_id' => 207
			],
			[
				'id' => 3606,
				'card_id' => 1140,
				'unit_id' => 207
			],
			[
				'id' => 3607,
				'card_id' => 1141,
				'unit_id' => 207
			],
			[
				'id' => 3608,
				'card_id' => 1142,
				'unit_id' => 207
			],
			[
				'id' => 3609,
				'card_id' => 1143,
				'unit_id' => 207
			],
			[
				'id' => 3610,
				'card_id' => 1144,
				'unit_id' => 207
			],
			[
				'id' => 3611,
				'card_id' => 1125,
				'unit_id' => 207
			],
			[
				'id' => 3612,
				'card_id' => 1146,
				'unit_id' => 207
			],
			[
				'id' => 3613,
				'card_id' => 1127,
				'unit_id' => 207
			],
			[
				'id' => 3614,
				'card_id' => 1148,
				'unit_id' => 207
			],
			[
				'id' => 3615,
				'card_id' => 1129,
				'unit_id' => 207
			],
			[
				'id' => 3616,
				'card_id' => 1150,
				'unit_id' => 207
			],
			[
				'id' => 3617,
				'card_id' => 1131,
				'unit_id' => 207
			],
			[
				'id' => 3618,
				'card_id' => 1152,
				'unit_id' => 207
			],
			[
				'id' => 3619,
				'card_id' => 565,
				'unit_id' => 208
			],
			[
				'id' => 3620,
				'card_id' => 248,
				'unit_id' => 208
			],
			[
				'id' => 3621,
				'card_id' => 213,
				'unit_id' => 208
			],
			[
				'id' => 3622,
				'card_id' => 4117,
				'unit_id' => 208
			],
			[
				'id' => 3623,
				'card_id' => 1155,
				'unit_id' => 208
			],
			[
				'id' => 3624,
				'card_id' => 1190,
				'unit_id' => 208
			],
			[
				'id' => 3625,
				'card_id' => 210,
				'unit_id' => 208
			],
			[
				'id' => 3626,
				'card_id' => 1194,
				'unit_id' => 208
			],
			[
				'id' => 3627,
				'card_id' => 4118,
				'unit_id' => 208
			],
			[
				'id' => 3628,
				'card_id' => 4119,
				'unit_id' => 208
			],
			[
				'id' => 3629,
				'card_id' => 1172,
				'unit_id' => 208
			],
			[
				'id' => 3630,
				'card_id' => 1173,
				'unit_id' => 208
			],
			[
				'id' => 3631,
				'card_id' => 1174,
				'unit_id' => 208
			],
			[
				'id' => 3632,
				'card_id' => 1175,
				'unit_id' => 208
			],
			[
				'id' => 3633,
				'card_id' => 1176,
				'unit_id' => 208
			],
			[
				'id' => 3634,
				'card_id' => 1177,
				'unit_id' => 208
			],
			[
				'id' => 3635,
				'card_id' => 1163,
				'unit_id' => 208
			],
			[
				'id' => 3636,
				'card_id' => 1179,
				'unit_id' => 208
			],
			[
				'id' => 3637,
				'card_id' => 1180,
				'unit_id' => 208
			],
			[
				'id' => 3638,
				'card_id' => 1181,
				'unit_id' => 208
			],
			[
				'id' => 3639,
				'card_id' => 1182,
				'unit_id' => 208
			],
			[
				'id' => 3640,
				'card_id' => 481,
				'unit_id' => 208
			],
			[
				'id' => 3641,
				'card_id' => 1184,
				'unit_id' => 208
			],
			[
				'id' => 3642,
				'card_id' => 1185,
				'unit_id' => 208
			],
			[
				'id' => 3643,
				'card_id' => 1186,
				'unit_id' => 208
			],
			[
				'id' => 3644,
				'card_id' => 1187,
				'unit_id' => 208
			],
			[
				'id' => 3645,
				'card_id' => 3,
				'unit_id' => 208
			],
			[
				'id' => 3646,
				'card_id' => 1189,
				'unit_id' => 208
			],
			[
				'id' => 3647,
				'card_id' => 1191,
				'unit_id' => 208
			],
			[
				'id' => 3648,
				'card_id' => 1168,
				'unit_id' => 208
			],
			[
				'id' => 3649,
				'card_id' => 1193,
				'unit_id' => 208
			],
			[
				'id' => 3650,
				'card_id' => 1195,
				'unit_id' => 208
			],
			[
				'id' => 3651,
				'card_id' => 1196,
				'unit_id' => 208
			],
			[
				'id' => 3652,
				'card_id' => 1197,
				'unit_id' => 208
			],
			[
				'id' => 3653,
				'card_id' => 1198,
				'unit_id' => 208
			],
			[
				'id' => 3654,
				'card_id' => 1080,
				'unit_id' => 209
			],
			[
				'id' => 3655,
				'card_id' => 1200,
				'unit_id' => 209
			],
			[
				'id' => 3656,
				'card_id' => 1203,
				'unit_id' => 209
			],
			[
				'id' => 3657,
				'card_id' => 1204,
				'unit_id' => 209
			],
			[
				'id' => 3658,
				'card_id' => 993,
				'unit_id' => 209
			],
			[
				'id' => 3659,
				'card_id' => 1208,
				'unit_id' => 209
			],
			[
				'id' => 3660,
				'card_id' => 1211,
				'unit_id' => 209
			],
			[
				'id' => 3661,
				'card_id' => 1212,
				'unit_id' => 209
			],
			[
				'id' => 3662,
				'card_id' => 1215,
				'unit_id' => 209
			],
			[
				'id' => 3663,
				'card_id' => 1216,
				'unit_id' => 209
			],
			[
				'id' => 3664,
				'card_id' => 996,
				'unit_id' => 209
			],
			[
				'id' => 3665,
				'card_id' => 1220,
				'unit_id' => 209
			],
			[
				'id' => 3666,
				'card_id' => 1229,
				'unit_id' => 209
			],
			[
				'id' => 3667,
				'card_id' => 300,
				'unit_id' => 209
			],
			[
				'id' => 3668,
				'card_id' => 1233,
				'unit_id' => 209
			],
			[
				'id' => 3669,
				'card_id' => 1234,
				'unit_id' => 209
			],
			[
				'id' => 3670,
				'card_id' => 1209,
				'unit_id' => 209
			],
			[
				'id' => 3671,
				'card_id' => 1210,
				'unit_id' => 209
			],
			[
				'id' => 3672,
				'card_id' => 1213,
				'unit_id' => 209
			],
			[
				'id' => 3673,
				'card_id' => 1214,
				'unit_id' => 209
			],
			[
				'id' => 3674,
				'card_id' => 1217,
				'unit_id' => 209
			],
			[
				'id' => 3675,
				'card_id' => 1218,
				'unit_id' => 209
			],
			[
				'id' => 3676,
				'card_id' => 1221,
				'unit_id' => 209
			],
			[
				'id' => 3677,
				'card_id' => 1222,
				'unit_id' => 209
			],
			[
				'id' => 3678,
				'card_id' => 1020,
				'unit_id' => 209
			],
			[
				'id' => 3679,
				'card_id' => 1224,
				'unit_id' => 209
			],
			[
				'id' => 3680,
				'card_id' => 1225,
				'unit_id' => 209
			],
			[
				'id' => 3681,
				'card_id' => 1226,
				'unit_id' => 209
			],
			[
				'id' => 3682,
				'card_id' => 1227,
				'unit_id' => 209
			],
			[
				'id' => 3683,
				'card_id' => 1228,
				'unit_id' => 209
			],
			[
				'id' => 3684,
				'card_id' => 1231,
				'unit_id' => 209
			],
			[
				'id' => 3685,
				'card_id' => 1232,
				'unit_id' => 209
			],
			[
				'id' => 3686,
				'card_id' => 978,
				'unit_id' => 209
			],
			[
				'id' => 3687,
				'card_id' => 1236,
				'unit_id' => 209
			],
			[
				'id' => 3688,
				'card_id' => 975,
				'unit_id' => 209
			],
			[
				'id' => 3689,
				'card_id' => 1202,
				'unit_id' => 209
			],
			[
				'id' => 3690,
				'card_id' => 1205,
				'unit_id' => 209
			],
			[
				'id' => 3691,
				'card_id' => 1206,
				'unit_id' => 209
			],
			[
				'id' => 3692,
				'card_id' => 4120,
				'unit_id' => 209
			],
			[
				'id' => 3693,
				'card_id' => 4128,
				'unit_id' => 209
			],
			[
				'id' => 3694,
				'card_id' => 4122,
				'unit_id' => 209
			],
			[
				'id' => 3695,
				'card_id' => 4123,
				'unit_id' => 209
			],
			[
				'id' => 3696,
				'card_id' => 4124,
				'unit_id' => 209
			],
			[
				'id' => 3697,
				'card_id' => 4125,
				'unit_id' => 209
			],
			[
				'id' => 3698,
				'card_id' => 4126,
				'unit_id' => 209
			],
			[
				'id' => 3699,
				'card_id' => 4127,
				'unit_id' => 209
			],
			[
				'id' => 3700,
				'card_id' => 1080,
				'unit_id' => 210
			],
			[
				'id' => 3701,
				'card_id' => 1242,
				'unit_id' => 210
			],
			[
				'id' => 3702,
				'card_id' => 1203,
				'unit_id' => 210
			],
			[
				'id' => 3703,
				'card_id' => 1246,
				'unit_id' => 210
			],
			[
				'id' => 3704,
				'card_id' => 993,
				'unit_id' => 210
			],
			[
				'id' => 3705,
				'card_id' => 1250,
				'unit_id' => 210
			],
			[
				'id' => 3706,
				'card_id' => 1211,
				'unit_id' => 210
			],
			[
				'id' => 3707,
				'card_id' => 1254,
				'unit_id' => 210
			],
			[
				'id' => 3708,
				'card_id' => 1215,
				'unit_id' => 210
			],
			[
				'id' => 3709,
				'card_id' => 1258,
				'unit_id' => 210
			],
			[
				'id' => 3710,
				'card_id' => 996,
				'unit_id' => 210
			],
			[
				'id' => 3711,
				'card_id' => 1262,
				'unit_id' => 210
			],
			[
				'id' => 3712,
				'card_id' => 1229,
				'unit_id' => 210
			],
			[
				'id' => 3713,
				'card_id' => 1272,
				'unit_id' => 210
			],
			[
				'id' => 3714,
				'card_id' => 975,
				'unit_id' => 210
			],
			[
				'id' => 3715,
				'card_id' => 1244,
				'unit_id' => 210
			],
			[
				'id' => 3716,
				'card_id' => 1205,
				'unit_id' => 210
			],
			[
				'id' => 3717,
				'card_id' => 1248,
				'unit_id' => 210
			],
			[
				'id' => 3718,
				'card_id' => 1209,
				'unit_id' => 210
			],
			[
				'id' => 3719,
				'card_id' => 1252,
				'unit_id' => 210
			],
			[
				'id' => 3720,
				'card_id' => 1213,
				'unit_id' => 210
			],
			[
				'id' => 3721,
				'card_id' => 1256,
				'unit_id' => 210
			],
			[
				'id' => 3722,
				'card_id' => 1217,
				'unit_id' => 210
			],
			[
				'id' => 3723,
				'card_id' => 1260,
				'unit_id' => 210
			],
			[
				'id' => 3724,
				'card_id' => 1221,
				'unit_id' => 210
			],
			[
				'id' => 3725,
				'card_id' => 1264,
				'unit_id' => 210
			],
			[
				'id' => 3726,
				'card_id' => 1227,
				'unit_id' => 210
			],
			[
				'id' => 3727,
				'card_id' => 1266,
				'unit_id' => 210
			],
			[
				'id' => 3728,
				'card_id' => 1225,
				'unit_id' => 210
			],
			[
				'id' => 3729,
				'card_id' => 1268,
				'unit_id' => 210
			],
			[
				'id' => 3730,
				'card_id' => 1231,
				'unit_id' => 210
			],
			[
				'id' => 3731,
				'card_id' => 1270,
				'unit_id' => 210
			],
			[
				'id' => 3732,
				'card_id' => 1020,
				'unit_id' => 210
			],
			[
				'id' => 3733,
				'card_id' => 1238,
				'unit_id' => 210
			],
			[
				'id' => 3734,
				'card_id' => 978,
				'unit_id' => 210
			],
			[
				'id' => 3735,
				'card_id' => 1240,
				'unit_id' => 210
			],
			[
				'id' => 3736,
				'card_id' => 4129,
				'unit_id' => 210
			],
			[
				'id' => 3737,
				'card_id' => 4130,
				'unit_id' => 210
			],
			[
				'id' => 3738,
				'card_id' => 4131,
				'unit_id' => 210
			],
			[
				'id' => 3739,
				'card_id' => 4132,
				'unit_id' => 210
			],
			[
				'id' => 3740,
				'card_id' => 4133,
				'unit_id' => 210
			],
			[
				'id' => 3741,
				'card_id' => 4134,
				'unit_id' => 210
			],
			[
				'id' => 3742,
				'card_id' => 4135,
				'unit_id' => 210
			],
			[
				'id' => 3743,
				'card_id' => 4136,
				'unit_id' => 210
			],
			[
				'id' => 3752,
				'card_id' => 1309,
				'unit_id' => 212
			],
			[
				'id' => 3753,
				'card_id' => 1307,
				'unit_id' => 212
			],
			[
				'id' => 3754,
				'card_id' => 1308,
				'unit_id' => 212
			],
			[
				'id' => 3755,
				'card_id' => 1312,
				'unit_id' => 212
			],
			[
				'id' => 3756,
				'card_id' => 1310,
				'unit_id' => 212
			],
			[
				'id' => 3757,
				'card_id' => 1311,
				'unit_id' => 212
			],
			[
				'id' => 3758,
				'card_id' => 1315,
				'unit_id' => 212
			],
			[
				'id' => 3759,
				'card_id' => 1313,
				'unit_id' => 212
			],
			[
				'id' => 3760,
				'card_id' => 1314,
				'unit_id' => 212
			],
			[
				'id' => 3761,
				'card_id' => 1318,
				'unit_id' => 212
			],
			[
				'id' => 3762,
				'card_id' => 1316,
				'unit_id' => 212
			],
			[
				'id' => 3763,
				'card_id' => 1317,
				'unit_id' => 212
			],
			[
				'id' => 3764,
				'card_id' => 1321,
				'unit_id' => 212
			],
			[
				'id' => 3765,
				'card_id' => 1319,
				'unit_id' => 212
			],
			[
				'id' => 3766,
				'card_id' => 1320,
				'unit_id' => 212
			],
			[
				'id' => 3767,
				'card_id' => 1077,
				'unit_id' => 212
			],
			[
				'id' => 3768,
				'card_id' => 1322,
				'unit_id' => 212
			],
			[
				'id' => 3769,
				'card_id' => 1323,
				'unit_id' => 212
			],
			[
				'id' => 3770,
				'card_id' => 1327,
				'unit_id' => 212
			],
			[
				'id' => 3771,
				'card_id' => 1325,
				'unit_id' => 212
			],
			[
				'id' => 3772,
				'card_id' => 1326,
				'unit_id' => 212
			],
			[
				'id' => 3773,
				'card_id' => 4146,
				'unit_id' => 212
			],
			[
				'id' => 3774,
				'card_id' => 1328,
				'unit_id' => 212
			],
			[
				'id' => 3775,
				'card_id' => 1329,
				'unit_id' => 212
			],
			[
				'id' => 3776,
				'card_id' => 1333,
				'unit_id' => 212
			],
			[
				'id' => 3777,
				'card_id' => 1331,
				'unit_id' => 212
			],
			[
				'id' => 3778,
				'card_id' => 1332,
				'unit_id' => 212
			],
			[
				'id' => 3779,
				'card_id' => 1336,
				'unit_id' => 212
			],
			[
				'id' => 3780,
				'card_id' => 4147,
				'unit_id' => 212
			],
			[
				'id' => 3781,
				'card_id' => 1335,
				'unit_id' => 212
			],
			[
				'id' => 3782,
				'card_id' => 1339,
				'unit_id' => 212
			],
			[
				'id' => 3783,
				'card_id' => 1337,
				'unit_id' => 212
			],
			[
				'id' => 3784,
				'card_id' => 1338,
				'unit_id' => 212
			],
			[
				'id' => 3785,
				'card_id' => 1342,
				'unit_id' => 212
			],
			[
				'id' => 3786,
				'card_id' => 1340,
				'unit_id' => 212
			],
			[
				'id' => 3787,
				'card_id' => 1341,
				'unit_id' => 212
			],
			[
				'id' => 3788,
				'card_id' => 1334,
				'unit_id' => 212
			],
			[
				'id' => 3789,
				'card_id' => 1345,
				'unit_id' => 212
			],
			[
				'id' => 3790,
				'card_id' => 1343,
				'unit_id' => 212
			],
			[
				'id' => 3791,
				'card_id' => 1344,
				'unit_id' => 212
			],
			[
				'id' => 3792,
				'card_id' => 1348,
				'unit_id' => 212
			],
			[
				'id' => 3793,
				'card_id' => 1346,
				'unit_id' => 212
			],
			[
				'id' => 3794,
				'card_id' => 1347,
				'unit_id' => 212
			],
			[
				'id' => 3795,
				'card_id' => 1351,
				'unit_id' => 212
			],
			[
				'id' => 3796,
				'card_id' => 1349,
				'unit_id' => 212
			],
			[
				'id' => 3797,
				'card_id' => 1350,
				'unit_id' => 212
			],
			[
				'id' => 3798,
				'card_id' => 1354,
				'unit_id' => 212
			],
			[
				'id' => 3799,
				'card_id' => 1352,
				'unit_id' => 212
			],
			[
				'id' => 3800,
				'card_id' => 1353,
				'unit_id' => 212
			],
			[
				'id' => 3801,
				'card_id' => 1357,
				'unit_id' => 212
			],
			[
				'id' => 3802,
				'card_id' => 1355,
				'unit_id' => 212
			],
			[
				'id' => 3803,
				'card_id' => 1356,
				'unit_id' => 212
			],
			[
				'id' => 3804,
				'card_id' => 1360,
				'unit_id' => 213
			],
			[
				'id' => 3805,
				'card_id' => 1361,
				'unit_id' => 213
			],
			[
				'id' => 3806,
				'card_id' => 330,
				'unit_id' => 213
			],
			[
				'id' => 3807,
				'card_id' => 331,
				'unit_id' => 213
			],
			[
				'id' => 3808,
				'card_id' => 1372,
				'unit_id' => 213
			],
			[
				'id' => 3809,
				'card_id' => 1373,
				'unit_id' => 213
			],
			[
				'id' => 3810,
				'card_id' => 4148,
				'unit_id' => 213
			],
			[
				'id' => 3811,
				'card_id' => 4149,
				'unit_id' => 213
			],
			[
				'id' => 3812,
				'card_id' => 4150,
				'unit_id' => 213
			],
			[
				'id' => 3813,
				'card_id' => 4151,
				'unit_id' => 213
			],
			[
				'id' => 3814,
				'card_id' => 1358,
				'unit_id' => 213
			],
			[
				'id' => 3815,
				'card_id' => 1374,
				'unit_id' => 213
			],
			[
				'id' => 3816,
				'card_id' => 1383,
				'unit_id' => 213
			],
			[
				'id' => 3817,
				'card_id' => 1364,
				'unit_id' => 213
			],
			[
				'id' => 3818,
				'card_id' => 1363,
				'unit_id' => 213
			],
			[
				'id' => 3819,
				'card_id' => 568,
				'unit_id' => 214
			],
			[
				'id' => 3820,
				'card_id' => 553,
				'unit_id' => 214
			],
			[
				'id' => 3821,
				'card_id' => 1390,
				'unit_id' => 214
			],
			[
				'id' => 3822,
				'card_id' => 1391,
				'unit_id' => 214
			],
			[
				'id' => 3823,
				'card_id' => 1393,
				'unit_id' => 214
			],
			[
				'id' => 3824,
				'card_id' => 1394,
				'unit_id' => 214
			],
			[
				'id' => 3825,
				'card_id' => 1395,
				'unit_id' => 214
			],
			[
				'id' => 3826,
				'card_id' => 1396,
				'unit_id' => 214
			],
			[
				'id' => 3827,
				'card_id' => 1392,
				'unit_id' => 214
			],
			[
				'id' => 3828,
				'card_id' => 198,
				'unit_id' => 214
			],
			[
				'id' => 3829,
				'card_id' => 1398,
				'unit_id' => 214
			],
			[
				'id' => 3830,
				'card_id' => 471,
				'unit_id' => 214
			],
			[
				'id' => 3831,
				'card_id' => 1076,
				'unit_id' => 214
			],
			[
				'id' => 3832,
				'card_id' => 445,
				'unit_id' => 214
			],
			[
				'id' => 3833,
				'card_id' => 557,
				'unit_id' => 214
			],
			[
				'id' => 3834,
				'card_id' => 1403,
				'unit_id' => 214
			],
			[
				'id' => 3835,
				'card_id' => 1404,
				'unit_id' => 214
			],
			[
				'id' => 3836,
				'card_id' => 1405,
				'unit_id' => 214
			],
			[
				'id' => 3837,
				'card_id' => 1406,
				'unit_id' => 214
			],
			[
				'id' => 3838,
				'card_id' => 1407,
				'unit_id' => 214
			],
			[
				'id' => 3839,
				'card_id' => 1408,
				'unit_id' => 214
			],
			[
				'id' => 3840,
				'card_id' => 1409,
				'unit_id' => 214
			],
			[
				'id' => 3841,
				'card_id' => 1410,
				'unit_id' => 214
			],
			[
				'id' => 3842,
				'card_id' => 1411,
				'unit_id' => 214
			],
			[
				'id' => 3843,
				'card_id' => 1412,
				'unit_id' => 214
			],
			[
				'id' => 3844,
				'card_id' => 1413,
				'unit_id' => 214
			],
			[
				'id' => 3845,
				'card_id' => 1414,
				'unit_id' => 214
			],
			[
				'id' => 3846,
				'card_id' => 1415,
				'unit_id' => 214
			],
			[
				'id' => 3847,
				'card_id' => 1416,
				'unit_id' => 214
			],
			[
				'id' => 3848,
				'card_id' => 1417,
				'unit_id' => 214
			],
			[
				'id' => 3849,
				'card_id' => 1418,
				'unit_id' => 214
			],
			[
				'id' => 3850,
				'card_id' => 407,
				'unit_id' => 214
			],
			[
				'id' => 3851,
				'card_id' => 1420,
				'unit_id' => 214
			],
			[
				'id' => 3852,
				'card_id' => 1073,
				'unit_id' => 214
			],
			[
				'id' => 3853,
				'card_id' => 1422,
				'unit_id' => 214
			],
			[
				'id' => 3854,
				'card_id' => 1309,
				'unit_id' => 215
			],
			[
				'id' => 3855,
				'card_id' => 1307,
				'unit_id' => 215
			],
			[
				'id' => 3856,
				'card_id' => 1308,
				'unit_id' => 215
			],
			[
				'id' => 3857,
				'card_id' => 1312,
				'unit_id' => 215
			],
			[
				'id' => 3858,
				'card_id' => 1310,
				'unit_id' => 215
			],
			[
				'id' => 3859,
				'card_id' => 1311,
				'unit_id' => 215
			],
			[
				'id' => 3860,
				'card_id' => 1315,
				'unit_id' => 215
			],
			[
				'id' => 3861,
				'card_id' => 1313,
				'unit_id' => 215
			],
			[
				'id' => 3862,
				'card_id' => 1314,
				'unit_id' => 215
			],
			[
				'id' => 3863,
				'card_id' => 1321,
				'unit_id' => 215
			],
			[
				'id' => 3864,
				'card_id' => 1319,
				'unit_id' => 215
			],
			[
				'id' => 3865,
				'card_id' => 1320,
				'unit_id' => 215
			],
			[
				'id' => 3866,
				'card_id' => 1327,
				'unit_id' => 215
			],
			[
				'id' => 3867,
				'card_id' => 1325,
				'unit_id' => 215
			],
			[
				'id' => 3868,
				'card_id' => 1326,
				'unit_id' => 215
			],
			[
				'id' => 3869,
				'card_id' => 1351,
				'unit_id' => 215
			],
			[
				'id' => 3870,
				'card_id' => 1349,
				'unit_id' => 215
			],
			[
				'id' => 3871,
				'card_id' => 1350,
				'unit_id' => 215
			],
			[
				'id' => 3872,
				'card_id' => 1357,
				'unit_id' => 215
			],
			[
				'id' => 3873,
				'card_id' => 1355,
				'unit_id' => 215
			],
			[
				'id' => 3874,
				'card_id' => 1356,
				'unit_id' => 215
			],
			[
				'id' => 3875,
				'card_id' => 1336,
				'unit_id' => 215
			],
			[
				'id' => 3876,
				'card_id' => 4147,
				'unit_id' => 215
			],
			[
				'id' => 3877,
				'card_id' => 1335,
				'unit_id' => 215
			],
			[
				'id' => 3878,
				'card_id' => 1333,
				'unit_id' => 215
			],
			[
				'id' => 3879,
				'card_id' => 1331,
				'unit_id' => 215
			],
			[
				'id' => 3880,
				'card_id' => 1332,
				'unit_id' => 215
			],
			[
				'id' => 3881,
				'card_id' => 4146,
				'unit_id' => 215
			],
			[
				'id' => 3882,
				'card_id' => 1328,
				'unit_id' => 215
			],
			[
				'id' => 3883,
				'card_id' => 1329,
				'unit_id' => 215
			],
			[
				'id' => 3884,
				'card_id' => 1342,
				'unit_id' => 215
			],
			[
				'id' => 3885,
				'card_id' => 1340,
				'unit_id' => 215
			],
			[
				'id' => 3886,
				'card_id' => 1341,
				'unit_id' => 215
			],
			[
				'id' => 3887,
				'card_id' => 1272,
				'unit_id' => 215
			],
			[
				'id' => 3888,
				'card_id' => 300,
				'unit_id' => 215
			],
			[
				'id' => 3889,
				'card_id' => 297,
				'unit_id' => 216
			],
			[
				'id' => 3890,
				'card_id' => 298,
				'unit_id' => 216
			],
			[
				'id' => 3891,
				'card_id' => 304,
				'unit_id' => 216
			],
			[
				'id' => 3892,
				'card_id' => 300,
				'unit_id' => 216
			],
			[
				'id' => 3893,
				'card_id' => 302,
				'unit_id' => 216
			],
			[
				'id' => 3894,
				'card_id' => 4255,
				'unit_id' => 216
			],
			[
				'id' => 3895,
				'card_id' => 299,
				'unit_id' => 216
			],
			[
				'id' => 3896,
				'card_id' => 301,
				'unit_id' => 216
			],
			[
				'id' => 3897,
				'card_id' => 311,
				'unit_id' => 216
			],
			[
				'id' => 3898,
				'card_id' => 307,
				'unit_id' => 216
			],
			[
				'id' => 3899,
				'card_id' => 309,
				'unit_id' => 216
			],
			[
				'id' => 3900,
				'card_id' => 306,
				'unit_id' => 216
			],
			[
				'id' => 3901,
				'card_id' => 308,
				'unit_id' => 216
			],
			[
				'id' => 3902,
				'card_id' => 310,
				'unit_id' => 216
			],
			[
				'id' => 3903,
				'card_id' => 312,
				'unit_id' => 216
			],
			[
				'id' => 3904,
				'card_id' => 314,
				'unit_id' => 216
			],
			[
				'id' => 3905,
				'card_id' => 316,
				'unit_id' => 216
			],
			[
				'id' => 3906,
				'card_id' => 313,
				'unit_id' => 216
			],
			[
				'id' => 3907,
				'card_id' => 315,
				'unit_id' => 216
			],
			[
				'id' => 3908,
				'card_id' => 317,
				'unit_id' => 216
			],
			[
				'id' => 3909,
				'card_id' => 318,
				'unit_id' => 216
			],
			[
				'id' => 3910,
				'card_id' => 320,
				'unit_id' => 216
			],
			[
				'id' => 3911,
				'card_id' => 321,
				'unit_id' => 216
			],
			[
				'id' => 3912,
				'card_id' => 323,
				'unit_id' => 216
			],
			[
				'id' => 3913,
				'card_id' => 324,
				'unit_id' => 216
			],
			[
				'id' => 3914,
				'card_id' => 325,
				'unit_id' => 216
			],
			[
				'id' => 3915,
				'card_id' => 326,
				'unit_id' => 216
			],
			[
				'id' => 3916,
				'card_id' => 327,
				'unit_id' => 216
			],
			[
				'id' => 3917,
				'card_id' => 328,
				'unit_id' => 216
			],
			[
				'id' => 3918,
				'card_id' => 330,
				'unit_id' => 216
			],
			[
				'id' => 3919,
				'card_id' => 331,
				'unit_id' => 216
			],
			[
				'id' => 3920,
				'card_id' => 329,
				'unit_id' => 216
			],
			[
				'id' => 3921,
				'card_id' => 1460,
				'unit_id' => 217
			],
			[
				'id' => 3922,
				'card_id' => 1461,
				'unit_id' => 217
			],
			[
				'id' => 3923,
				'card_id' => 1462,
				'unit_id' => 217
			],
			[
				'id' => 3924,
				'card_id' => 1463,
				'unit_id' => 217
			],
			[
				'id' => 3925,
				'card_id' => 1464,
				'unit_id' => 217
			],
			[
				'id' => 3926,
				'card_id' => 1465,
				'unit_id' => 217
			],
			[
				'id' => 3927,
				'card_id' => 1466,
				'unit_id' => 217
			],
			[
				'id' => 3928,
				'card_id' => 1467,
				'unit_id' => 217
			],
			[
				'id' => 3929,
				'card_id' => 1468,
				'unit_id' => 217
			],
			[
				'id' => 3930,
				'card_id' => 1469,
				'unit_id' => 217
			],
			[
				'id' => 3931,
				'card_id' => 1470,
				'unit_id' => 217
			],
			[
				'id' => 3932,
				'card_id' => 1471,
				'unit_id' => 217
			],
			[
				'id' => 3933,
				'card_id' => 1472,
				'unit_id' => 217
			],
			[
				'id' => 3934,
				'card_id' => 1473,
				'unit_id' => 217
			],
			[
				'id' => 3935,
				'card_id' => 1474,
				'unit_id' => 217
			],
			[
				'id' => 3936,
				'card_id' => 1475,
				'unit_id' => 217
			],
			[
				'id' => 3937,
				'card_id' => 1476,
				'unit_id' => 217
			],
			[
				'id' => 3938,
				'card_id' => 1477,
				'unit_id' => 217
			],
			[
				'id' => 3939,
				'card_id' => 1478,
				'unit_id' => 217
			],
			[
				'id' => 3940,
				'card_id' => 1479,
				'unit_id' => 217
			],
			[
				'id' => 3941,
				'card_id' => 1480,
				'unit_id' => 217
			],
			[
				'id' => 3942,
				'card_id' => 1481,
				'unit_id' => 217
			],
			[
				'id' => 3943,
				'card_id' => 1482,
				'unit_id' => 217
			],
			[
				'id' => 3944,
				'card_id' => 1483,
				'unit_id' => 217
			],
			[
				'id' => 3945,
				'card_id' => 1484,
				'unit_id' => 217
			],
			[
				'id' => 3946,
				'card_id' => 1485,
				'unit_id' => 217
			],
			[
				'id' => 3947,
				'card_id' => 1486,
				'unit_id' => 217
			],
			[
				'id' => 3948,
				'card_id' => 1487,
				'unit_id' => 217
			],
			[
				'id' => 3949,
				'card_id' => 1488,
				'unit_id' => 217
			],
			[
				'id' => 3950,
				'card_id' => 1489,
				'unit_id' => 217
			],
			[
				'id' => 3951,
				'card_id' => 1490,
				'unit_id' => 217
			],
			[
				'id' => 3952,
				'card_id' => 1491,
				'unit_id' => 217
			],
			[
				'id' => 3953,
				'card_id' => 1492,
				'unit_id' => 217
			],
			[
				'id' => 3954,
				'card_id' => 1493,
				'unit_id' => 217
			],
			[
				'id' => 3955,
				'card_id' => 1494,
				'unit_id' => 217
			],
			[
				'id' => 3956,
				'card_id' => 1495,
				'unit_id' => 217
			],
			[
				'id' => 3957,
				'card_id' => 1496,
				'unit_id' => 218
			],
			[
				'id' => 3958,
				'card_id' => 1497,
				'unit_id' => 218
			],
			[
				'id' => 3959,
				'card_id' => 1498,
				'unit_id' => 218
			],
			[
				'id' => 3960,
				'card_id' => 1499,
				'unit_id' => 218
			],
			[
				'id' => 3961,
				'card_id' => 1500,
				'unit_id' => 218
			],
			[
				'id' => 3962,
				'card_id' => 1501,
				'unit_id' => 218
			],
			[
				'id' => 3963,
				'card_id' => 1502,
				'unit_id' => 218
			],
			[
				'id' => 3964,
				'card_id' => 1503,
				'unit_id' => 218
			],
			[
				'id' => 3965,
				'card_id' => 1506,
				'unit_id' => 218
			],
			[
				'id' => 3966,
				'card_id' => 1507,
				'unit_id' => 218
			],
			[
				'id' => 3967,
				'card_id' => 1509,
				'unit_id' => 218
			],
			[
				'id' => 3968,
				'card_id' => 1510,
				'unit_id' => 218
			],
			[
				'id' => 3969,
				'card_id' => 1511,
				'unit_id' => 218
			],
			[
				'id' => 3970,
				'card_id' => 1513,
				'unit_id' => 218
			],
			[
				'id' => 3971,
				'card_id' => 1514,
				'unit_id' => 218
			],
			[
				'id' => 3972,
				'card_id' => 1515,
				'unit_id' => 218
			],
			[
				'id' => 3973,
				'card_id' => 1516,
				'unit_id' => 219
			],
			[
				'id' => 3974,
				'card_id' => 1518,
				'unit_id' => 219
			],
			[
				'id' => 3975,
				'card_id' => 1519,
				'unit_id' => 219
			],
			[
				'id' => 3976,
				'card_id' => 1520,
				'unit_id' => 219
			],
			[
				'id' => 3977,
				'card_id' => 1522,
				'unit_id' => 219
			],
			[
				'id' => 3978,
				'card_id' => 1523,
				'unit_id' => 219
			],
			[
				'id' => 3979,
				'card_id' => 1524,
				'unit_id' => 219
			],
			[
				'id' => 3980,
				'card_id' => 1526,
				'unit_id' => 219
			],
			[
				'id' => 3981,
				'card_id' => 1527,
				'unit_id' => 219
			],
			[
				'id' => 3982,
				'card_id' => 1528,
				'unit_id' => 219
			],
			[
				'id' => 3983,
				'card_id' => 1530,
				'unit_id' => 219
			],
			[
				'id' => 3984,
				'card_id' => 1531,
				'unit_id' => 219
			],
			[
				'id' => 3985,
				'card_id' => 1532,
				'unit_id' => 219
			],
			[
				'id' => 3986,
				'card_id' => 1534,
				'unit_id' => 219
			],
			[
				'id' => 3987,
				'card_id' => 1535,
				'unit_id' => 219
			],
			[
				'id' => 3988,
				'card_id' => 1543,
				'unit_id' => 219
			],
			[
				'id' => 3989,
				'card_id' => 1547,
				'unit_id' => 219
			],
			[
				'id' => 3990,
				'card_id' => 1539,
				'unit_id' => 219
			],
			[
				'id' => 3991,
				'card_id' => 1536,
				'unit_id' => 219
			],
			[
				'id' => 3992,
				'card_id' => 1540,
				'unit_id' => 219
			],
			[
				'id' => 3993,
				'card_id' => 1549,
				'unit_id' => 219
			],
			[
				'id' => 3994,
				'card_id' => 1550,
				'unit_id' => 219
			],
			[
				'id' => 3995,
				'card_id' => 1542,
				'unit_id' => 219
			],
			[
				'id' => 3996,
				'card_id' => 1544,
				'unit_id' => 219
			],
			[
				'id' => 3997,
				'card_id' => 1541,
				'unit_id' => 219
			],
			[
				'id' => 3998,
				'card_id' => 1537,
				'unit_id' => 219
			],
			[
				'id' => 3999,
				'card_id' => 1538,
				'unit_id' => 219
			],
			[
				'id' => 4000,
				'card_id' => 1556,
				'unit_id' => 220
			],
			[
				'id' => 4001,
				'card_id' => 1558,
				'unit_id' => 220
			],
			[
				'id' => 4002,
				'card_id' => 1559,
				'unit_id' => 220
			],
			[
				'id' => 4003,
				'card_id' => 1560,
				'unit_id' => 220
			],
			[
				'id' => 4004,
				'card_id' => 1562,
				'unit_id' => 220
			],
			[
				'id' => 4005,
				'card_id' => 1563,
				'unit_id' => 220
			],
			[
				'id' => 4006,
				'card_id' => 1564,
				'unit_id' => 220
			],
			[
				'id' => 4007,
				'card_id' => 1566,
				'unit_id' => 220
			],
			[
				'id' => 4008,
				'card_id' => 1567,
				'unit_id' => 220
			],
			[
				'id' => 4009,
				'card_id' => 1568,
				'unit_id' => 220
			],
			[
				'id' => 4010,
				'card_id' => 1570,
				'unit_id' => 220
			],
			[
				'id' => 4011,
				'card_id' => 1571,
				'unit_id' => 220
			],
			[
				'id' => 4012,
				'card_id' => 1581,
				'unit_id' => 220
			],
			[
				'id' => 4013,
				'card_id' => 1578,
				'unit_id' => 220
			],
			[
				'id' => 4014,
				'card_id' => 1587,
				'unit_id' => 220
			],
			[
				'id' => 4015,
				'card_id' => 1577,
				'unit_id' => 220
			],
			[
				'id' => 4016,
				'card_id' => 1585,
				'unit_id' => 220
			],
			[
				'id' => 4017,
				'card_id' => 1576,
				'unit_id' => 220
			],
			[
				'id' => 4018,
				'card_id' => 1583,
				'unit_id' => 220
			],
			[
				'id' => 4019,
				'card_id' => 1579,
				'unit_id' => 220
			],
			[
				'id' => 4020,
				'card_id' => 1588,
				'unit_id' => 221
			],
			[
				'id' => 4021,
				'card_id' => 1591,
				'unit_id' => 221
			],
			[
				'id' => 4022,
				'card_id' => 1612,
				'unit_id' => 221
			],
			[
				'id' => 4023,
				'card_id' => 1601,
				'unit_id' => 221
			],
			[
				'id' => 4024,
				'card_id' => 1608,
				'unit_id' => 221
			],
			[
				'id' => 4025,
				'card_id' => 1616,
				'unit_id' => 221
			],
			[
				'id' => 4026,
				'card_id' => 1598,
				'unit_id' => 221
			],
			[
				'id' => 4027,
				'card_id' => 1613,
				'unit_id' => 221
			],
			[
				'id' => 4028,
				'card_id' => 1621,
				'unit_id' => 221
			],
			[
				'id' => 4029,
				'card_id' => 1594,
				'unit_id' => 221
			],
			[
				'id' => 4030,
				'card_id' => 1599,
				'unit_id' => 221
			],
			[
				'id' => 4031,
				'card_id' => 1614,
				'unit_id' => 221
			],
			[
				'id' => 4032,
				'card_id' => 1622,
				'unit_id' => 221
			],
			[
				'id' => 4033,
				'card_id' => 1615,
				'unit_id' => 221
			],
			[
				'id' => 4034,
				'card_id' => 442,
				'unit_id' => 222
			],
			[
				'id' => 4035,
				'card_id' => 1628,
				'unit_id' => 222
			],
			[
				'id' => 4036,
				'card_id' => 1632,
				'unit_id' => 222
			],
			[
				'id' => 4037,
				'card_id' => 1629,
				'unit_id' => 222
			],
			[
				'id' => 4038,
				'card_id' => 1638,
				'unit_id' => 222
			],
			[
				'id' => 4039,
				'card_id' => 1636,
				'unit_id' => 222
			],
			[
				'id' => 4040,
				'card_id' => 1633,
				'unit_id' => 222
			],
			[
				'id' => 4041,
				'card_id' => 1639,
				'unit_id' => 226
			],
			[
				'id' => 4042,
				'card_id' => 1640,
				'unit_id' => 226
			],
			[
				'id' => 4043,
				'card_id' => 1641,
				'unit_id' => 226
			],
			[
				'id' => 4044,
				'card_id' => 1643,
				'unit_id' => 226
			],
			[
				'id' => 4045,
				'card_id' => 1644,
				'unit_id' => 226
			],
			[
				'id' => 4046,
				'card_id' => 1645,
				'unit_id' => 226
			],
			[
				'id' => 4047,
				'card_id' => 1647,
				'unit_id' => 226
			],
			[
				'id' => 4048,
				'card_id' => 1648,
				'unit_id' => 226
			],
			[
				'id' => 4049,
				'card_id' => 1649,
				'unit_id' => 226
			],
			[
				'id' => 4050,
				'card_id' => 1651,
				'unit_id' => 226
			],
			[
				'id' => 4051,
				'card_id' => 1652,
				'unit_id' => 226
			],
			[
				'id' => 4052,
				'card_id' => 1653,
				'unit_id' => 226
			],
			[
				'id' => 4053,
				'card_id' => 1655,
				'unit_id' => 226
			],
			[
				'id' => 4054,
				'card_id' => 1656,
				'unit_id' => 226
			],
			[
				'id' => 4055,
				'card_id' => 1657,
				'unit_id' => 226
			],
			[
				'id' => 4056,
				'card_id' => 1659,
				'unit_id' => 226
			],
			[
				'id' => 4057,
				'card_id' => 1660,
				'unit_id' => 226
			],
			[
				'id' => 4058,
				'card_id' => 1661,
				'unit_id' => 226
			],
			[
				'id' => 4059,
				'card_id' => 1663,
				'unit_id' => 226
			],
			[
				'id' => 4060,
				'card_id' => 1448,
				'unit_id' => 226
			],
			[
				'id' => 4061,
				'card_id' => 1679,
				'unit_id' => 226
			],
			[
				'id' => 4062,
				'card_id' => 1451,
				'unit_id' => 226
			],
			[
				'id' => 4063,
				'card_id' => 1682,
				'unit_id' => 226
			],
			[
				'id' => 4064,
				'card_id' => 1298,
				'unit_id' => 226
			],
			[
				'id' => 4065,
				'card_id' => 1685,
				'unit_id' => 226
			],
			[
				'id' => 4066,
				'card_id' => 1445,
				'unit_id' => 226
			],
			[
				'id' => 4067,
				'card_id' => 1689,
				'unit_id' => 226
			],
			[
				'id' => 4068,
				'card_id' => 1690,
				'unit_id' => 226
			],
			[
				'id' => 4069,
				'card_id' => 1697,
				'unit_id' => 226
			],
			[
				'id' => 4070,
				'card_id' => 1698,
				'unit_id' => 226
			],
			[
				'id' => 4071,
				'card_id' => 4154,
				'unit_id' => 226
			],
			[
				'id' => 4072,
				'card_id' => 4156,
				'unit_id' => 226
			],
			[
				'id' => 4073,
				'card_id' => 4158,
				'unit_id' => 226
			],
			[
				'id' => 4074,
				'card_id' => 1741,
				'unit_id' => 227
			],
			[
				'id' => 4075,
				'card_id' => 1742,
				'unit_id' => 227
			],
			[
				'id' => 4076,
				'card_id' => 1743,
				'unit_id' => 227
			],
			[
				'id' => 4077,
				'card_id' => 1744,
				'unit_id' => 227
			],
			[
				'id' => 4078,
				'card_id' => 1745,
				'unit_id' => 227
			],
			[
				'id' => 4079,
				'card_id' => 1746,
				'unit_id' => 227
			],
			[
				'id' => 4080,
				'card_id' => 1747,
				'unit_id' => 227
			],
			[
				'id' => 4081,
				'card_id' => 681,
				'unit_id' => 227
			],
			[
				'id' => 4082,
				'card_id' => 1749,
				'unit_id' => 227
			],
			[
				'id' => 4083,
				'card_id' => 1750,
				'unit_id' => 227
			],
			[
				'id' => 4084,
				'card_id' => 1752,
				'unit_id' => 227
			],
			[
				'id' => 4085,
				'card_id' => 1751,
				'unit_id' => 227
			],
			[
				'id' => 4086,
				'card_id' => 1760,
				'unit_id' => 227
			],
			[
				'id' => 4087,
				'card_id' => 1761,
				'unit_id' => 227
			],
			[
				'id' => 4088,
				'card_id' => 1765,
				'unit_id' => 227
			],
			[
				'id' => 4089,
				'card_id' => 1763,
				'unit_id' => 227
			],
			[
				'id' => 4090,
				'card_id' => 1764,
				'unit_id' => 227
			],
			[
				'id' => 4091,
				'card_id' => 1758,
				'unit_id' => 227
			],
			[
				'id' => 4092,
				'card_id' => 1762,
				'unit_id' => 227
			],
			[
				'id' => 4093,
				'card_id' => 1756,
				'unit_id' => 227
			],
			[
				'id' => 4094,
				'card_id' => 1759,
				'unit_id' => 227
			],
			[
				'id' => 4095,
				'card_id' => 1766,
				'unit_id' => 229
			],
			[
				'id' => 4096,
				'card_id' => 1767,
				'unit_id' => 229
			],
			[
				'id' => 4097,
				'card_id' => 1768,
				'unit_id' => 229
			],
			[
				'id' => 4098,
				'card_id' => 1769,
				'unit_id' => 229
			],
			[
				'id' => 4099,
				'card_id' => 1770,
				'unit_id' => 229
			],
			[
				'id' => 4100,
				'card_id' => 1771,
				'unit_id' => 229
			],
			[
				'id' => 4101,
				'card_id' => 4165,
				'unit_id' => 229
			],
			[
				'id' => 4102,
				'card_id' => 1772,
				'unit_id' => 229
			],
			[
				'id' => 4103,
				'card_id' => 1773,
				'unit_id' => 229
			],
			[
				'id' => 4104,
				'card_id' => 1774,
				'unit_id' => 229
			],
			[
				'id' => 4105,
				'card_id' => 1775,
				'unit_id' => 229
			],
			[
				'id' => 4106,
				'card_id' => 1776,
				'unit_id' => 229
			],
			[
				'id' => 4107,
				'card_id' => 1777,
				'unit_id' => 229
			],
			[
				'id' => 4108,
				'card_id' => 1081,
				'unit_id' => 230
			],
			[
				'id' => 4109,
				'card_id' => 1796,
				'unit_id' => 230
			],
			[
				'id' => 4110,
				'card_id' => 1797,
				'unit_id' => 230
			],
			[
				'id' => 4111,
				'card_id' => 1798,
				'unit_id' => 230
			],
			[
				'id' => 4112,
				'card_id' => 1799,
				'unit_id' => 230
			],
			[
				'id' => 4113,
				'card_id' => 1800,
				'unit_id' => 230
			],
			[
				'id' => 4114,
				'card_id' => 1801,
				'unit_id' => 230
			],
			[
				'id' => 4115,
				'card_id' => 1802,
				'unit_id' => 230
			],
			[
				'id' => 4116,
				'card_id' => 1803,
				'unit_id' => 230
			],
			[
				'id' => 4117,
				'card_id' => 1804,
				'unit_id' => 230
			],
			[
				'id' => 4118,
				'card_id' => 1805,
				'unit_id' => 230
			],
			[
				'id' => 4119,
				'card_id' => 1806,
				'unit_id' => 230
			],
			[
				'id' => 4120,
				'card_id' => 1807,
				'unit_id' => 230
			],
			[
				'id' => 4121,
				'card_id' => 1808,
				'unit_id' => 230
			],
			[
				'id' => 4122,
				'card_id' => 1809,
				'unit_id' => 230
			],
			[
				'id' => 4123,
				'card_id' => 1810,
				'unit_id' => 230
			],
			[
				'id' => 4124,
				'card_id' => 4169,
				'unit_id' => 230
			],
			[
				'id' => 4125,
				'card_id' => 4170,
				'unit_id' => 230
			],
			[
				'id' => 4126,
				'card_id' => 4171,
				'unit_id' => 230
			],
			[
				'id' => 4127,
				'card_id' => 4172,
				'unit_id' => 230
			],
			[
				'id' => 4128,
				'card_id' => 4173,
				'unit_id' => 230
			],
			[
				'id' => 4129,
				'card_id' => 4174,
				'unit_id' => 230
			],
			[
				'id' => 4130,
				'card_id' => 4175,
				'unit_id' => 230
			],
			[
				'id' => 4131,
				'card_id' => 4176,
				'unit_id' => 230
			],
			[
				'id' => 4132,
				'card_id' => 1811,
				'unit_id' => 230
			],
			[
				'id' => 4133,
				'card_id' => 1812,
				'unit_id' => 230
			],
			[
				'id' => 4134,
				'card_id' => 1813,
				'unit_id' => 230
			],
			[
				'id' => 4135,
				'card_id' => 1814,
				'unit_id' => 230
			],
			[
				'id' => 4136,
				'card_id' => 1829,
				'unit_id' => 230
			],
			[
				'id' => 4137,
				'card_id' => 1830,
				'unit_id' => 230
			],
			[
				'id' => 4138,
				'card_id' => 1831,
				'unit_id' => 230
			],
			[
				'id' => 4139,
				'card_id' => 1832,
				'unit_id' => 230
			],
			[
				'id' => 4140,
				'card_id' => 1833,
				'unit_id' => 230
			],
			[
				'id' => 4141,
				'card_id' => 1834,
				'unit_id' => 230
			],
			[
				'id' => 4142,
				'card_id' => 1835,
				'unit_id' => 230
			],
			[
				'id' => 4143,
				'card_id' => 1836,
				'unit_id' => 230
			],
			[
				'id' => 4144,
				'card_id' => 1837,
				'unit_id' => 230
			],
			[
				'id' => 4145,
				'card_id' => 1838,
				'unit_id' => 230
			],
			[
				'id' => 4146,
				'card_id' => 1839,
				'unit_id' => 230
			],
			[
				'id' => 4147,
				'card_id' => 1840,
				'unit_id' => 230
			],
			[
				'id' => 4148,
				'card_id' => 1841,
				'unit_id' => 230
			],
			[
				'id' => 4149,
				'card_id' => 1842,
				'unit_id' => 230
			],
			[
				'id' => 4150,
				'card_id' => 4177,
				'unit_id' => 230
			],
			[
				'id' => 4151,
				'card_id' => 4178,
				'unit_id' => 230
			],
			[
				'id' => 4152,
				'card_id' => 4179,
				'unit_id' => 230
			],
			[
				'id' => 4153,
				'card_id' => 4180,
				'unit_id' => 230
			],
			[
				'id' => 4154,
				'card_id' => 4182,
				'unit_id' => 230
			],
			[
				'id' => 4155,
				'card_id' => 4183,
				'unit_id' => 230
			],
			[
				'id' => 4156,
				'card_id' => 4184,
				'unit_id' => 230
			],
			[
				'id' => 4157,
				'card_id' => 4185,
				'unit_id' => 230
			],
			[
				'id' => 4158,
				'card_id' => 1809,
				'unit_id' => 231
			],
			[
				'id' => 4159,
				'card_id' => 1844,
				'unit_id' => 231
			],
			[
				'id' => 4160,
				'card_id' => 1811,
				'unit_id' => 231
			],
			[
				'id' => 4161,
				'card_id' => 1846,
				'unit_id' => 231
			],
			[
				'id' => 4162,
				'card_id' => 1813,
				'unit_id' => 231
			],
			[
				'id' => 4163,
				'card_id' => 1848,
				'unit_id' => 231
			],
			[
				'id' => 4164,
				'card_id' => 1807,
				'unit_id' => 231
			],
			[
				'id' => 4165,
				'card_id' => 1850,
				'unit_id' => 231
			],
			[
				'id' => 4166,
				'card_id' => 1081,
				'unit_id' => 231
			],
			[
				'id' => 4167,
				'card_id' => 1852,
				'unit_id' => 231
			],
			[
				'id' => 4168,
				'card_id' => 1797,
				'unit_id' => 231
			],
			[
				'id' => 4169,
				'card_id' => 1854,
				'unit_id' => 231
			],
			[
				'id' => 4170,
				'card_id' => 1799,
				'unit_id' => 231
			],
			[
				'id' => 4171,
				'card_id' => 1856,
				'unit_id' => 231
			],
			[
				'id' => 4172,
				'card_id' => 1857,
				'unit_id' => 231
			],
			[
				'id' => 4173,
				'card_id' => 1858,
				'unit_id' => 231
			],
			[
				'id' => 4174,
				'card_id' => 4195,
				'unit_id' => 231
			],
			[
				'id' => 4175,
				'card_id' => 4196,
				'unit_id' => 231
			],
			[
				'id' => 4176,
				'card_id' => 4197,
				'unit_id' => 231
			],
			[
				'id' => 4177,
				'card_id' => 4198,
				'unit_id' => 231
			],
			[
				'id' => 4178,
				'card_id' => 4199,
				'unit_id' => 231
			],
			[
				'id' => 4179,
				'card_id' => 4200,
				'unit_id' => 231
			],
			[
				'id' => 4180,
				'card_id' => 4201,
				'unit_id' => 231
			],
			[
				'id' => 4181,
				'card_id' => 4202,
				'unit_id' => 231
			],
			[
				'id' => 4182,
				'card_id' => 1803,
				'unit_id' => 231
			],
			[
				'id' => 4183,
				'card_id' => 1860,
				'unit_id' => 231
			],
			[
				'id' => 4184,
				'card_id' => 1805,
				'unit_id' => 231
			],
			[
				'id' => 4185,
				'card_id' => 1862,
				'unit_id' => 231
			],
			[
				'id' => 4186,
				'card_id' => 1829,
				'unit_id' => 231
			],
			[
				'id' => 4187,
				'card_id' => 1864,
				'unit_id' => 231
			],
			[
				'id' => 4188,
				'card_id' => 1831,
				'unit_id' => 231
			],
			[
				'id' => 4189,
				'card_id' => 1866,
				'unit_id' => 231
			],
			[
				'id' => 4190,
				'card_id' => 1833,
				'unit_id' => 231
			],
			[
				'id' => 4191,
				'card_id' => 1868,
				'unit_id' => 231
			],
			[
				'id' => 4192,
				'card_id' => 1835,
				'unit_id' => 231
			],
			[
				'id' => 4193,
				'card_id' => 1870,
				'unit_id' => 231
			],
			[
				'id' => 4194,
				'card_id' => 1837,
				'unit_id' => 231
			],
			[
				'id' => 4195,
				'card_id' => 1872,
				'unit_id' => 231
			],
			[
				'id' => 4196,
				'card_id' => 1839,
				'unit_id' => 231
			],
			[
				'id' => 4197,
				'card_id' => 1874,
				'unit_id' => 231
			],
			[
				'id' => 4198,
				'card_id' => 1841,
				'unit_id' => 231
			],
			[
				'id' => 4199,
				'card_id' => 1876,
				'unit_id' => 231
			],
			[
				'id' => 4200,
				'card_id' => 4203,
				'unit_id' => 231
			],
			[
				'id' => 4201,
				'card_id' => 4204,
				'unit_id' => 231
			],
			[
				'id' => 4202,
				'card_id' => 4205,
				'unit_id' => 231
			],
			[
				'id' => 4203,
				'card_id' => 4206,
				'unit_id' => 231
			],
			[
				'id' => 4204,
				'card_id' => 4208,
				'unit_id' => 231
			],
			[
				'id' => 4205,
				'card_id' => 4209,
				'unit_id' => 231
			],
			[
				'id' => 4206,
				'card_id' => 4210,
				'unit_id' => 231
			],
			[
				'id' => 4207,
				'card_id' => 4211,
				'unit_id' => 231
			],
			[
				'id' => 4208,
				'card_id' => 1877,
				'unit_id' => 232
			],
			[
				'id' => 4209,
				'card_id' => 1878,
				'unit_id' => 232
			],
			[
				'id' => 4210,
				'card_id' => 1879,
				'unit_id' => 232
			],
			[
				'id' => 4211,
				'card_id' => 1880,
				'unit_id' => 232
			],
			[
				'id' => 4212,
				'card_id' => 4212,
				'unit_id' => 232
			],
			[
				'id' => 4213,
				'card_id' => 4213,
				'unit_id' => 232
			],
			[
				'id' => 4214,
				'card_id' => 4220,
				'unit_id' => 232
			],
			[
				'id' => 4215,
				'card_id' => 4215,
				'unit_id' => 232
			],
			[
				'id' => 4216,
				'card_id' => 4216,
				'unit_id' => 232
			],
			[
				'id' => 4217,
				'card_id' => 4214,
				'unit_id' => 232
			],
			[
				'id' => 4218,
				'card_id' => 4217,
				'unit_id' => 232
			],
			[
				'id' => 4219,
				'card_id' => 4219,
				'unit_id' => 232
			],
			[
				'id' => 4220,
				'card_id' => 1911,
				'unit_id' => 233
			],
			[
				'id' => 4221,
				'card_id' => 1912,
				'unit_id' => 233
			],
			[
				'id' => 4222,
				'card_id' => 1913,
				'unit_id' => 233
			],
			[
				'id' => 4223,
				'card_id' => 1914,
				'unit_id' => 233
			],
			[
				'id' => 4224,
				'card_id' => 1915,
				'unit_id' => 233
			],
			[
				'id' => 4225,
				'card_id' => 1916,
				'unit_id' => 233
			],
			[
				'id' => 4226,
				'card_id' => 1917,
				'unit_id' => 233
			],
			[
				'id' => 4227,
				'card_id' => 1918,
				'unit_id' => 233
			],
			[
				'id' => 4228,
				'card_id' => 1929,
				'unit_id' => 233
			],
			[
				'id' => 4229,
				'card_id' => 1930,
				'unit_id' => 233
			],
			[
				'id' => 4230,
				'card_id' => 1927,
				'unit_id' => 233
			],
			[
				'id' => 4231,
				'card_id' => 1932,
				'unit_id' => 233
			],
			[
				'id' => 4232,
				'card_id' => 1926,
				'unit_id' => 233
			],
			[
				'id' => 4233,
				'card_id' => 1919,
				'unit_id' => 233
			],
			[
				'id' => 4234,
				'card_id' => 1941,
				'unit_id' => 233
			],
			[
				'id' => 4235,
				'card_id' => 1942,
				'unit_id' => 233
			],
			[
				'id' => 4236,
				'card_id' => 1945,
				'unit_id' => 233
			],
			[
				'id' => 4237,
				'card_id' => 1952,
				'unit_id' => 233
			],
			[
				'id' => 4238,
				'card_id' => 1923,
				'unit_id' => 233
			],
			[
				'id' => 4239,
				'card_id' => 1944,
				'unit_id' => 233
			],
			[
				'id' => 4240,
				'card_id' => 1929,
				'unit_id' => 234
			],
			[
				'id' => 4241,
				'card_id' => 1958,
				'unit_id' => 234
			],
			[
				'id' => 4242,
				'card_id' => 1959,
				'unit_id' => 234
			],
			[
				'id' => 4243,
				'card_id' => 1960,
				'unit_id' => 234
			],
			[
				'id' => 4244,
				'card_id' => 1964,
				'unit_id' => 234
			],
			[
				'id' => 4245,
				'card_id' => 1943,
				'unit_id' => 234
			],
			[
				'id' => 4246,
				'card_id' => 1951,
				'unit_id' => 234
			],
			[
				'id' => 4247,
				'card_id' => 1974,
				'unit_id' => 234
			],
			[
				'id' => 4248,
				'card_id' => 1996,
				'unit_id' => 234
			],
			[
				'id' => 4249,
				'card_id' => 1933,
				'unit_id' => 234
			],
			[
				'id' => 4250,
				'card_id' => 4225,
				'unit_id' => 235
			],
			[
				'id' => 4251,
				'card_id' => 4221,
				'unit_id' => 235
			],
			[
				'id' => 4252,
				'card_id' => 2044,
				'unit_id' => 235
			],
			[
				'id' => 4253,
				'card_id' => 2041,
				'unit_id' => 235
			],
			[
				'id' => 4254,
				'card_id' => 2039,
				'unit_id' => 235
			],
			[
				'id' => 4255,
				'card_id' => 4223,
				'unit_id' => 235
			],
			[
				'id' => 4256,
				'card_id' => 2027,
				'unit_id' => 235
			],
			[
				'id' => 4257,
				'card_id' => 4224,
				'unit_id' => 235
			],
			[
				'id' => 4258,
				'card_id' => 4226,
				'unit_id' => 235
			],
			[
				'id' => 4259,
				'card_id' => 4222,
				'unit_id' => 235
			],
			[
				'id' => 4260,
				'card_id' => 2011,
				'unit_id' => 235
			],
			[
				'id' => 4261,
				'card_id' => 2048,
				'unit_id' => 235
			],
			[
				'id' => 4262,
				'card_id' => 4497,
				'unit_id' => 236
			],
			[
				'id' => 4263,
				'card_id' => 2051,
				'unit_id' => 236
			],
			[
				'id' => 4264,
				'card_id' => 2052,
				'unit_id' => 236
			],
			[
				'id' => 4265,
				'card_id' => 2053,
				'unit_id' => 236
			],
			[
				'id' => 4266,
				'card_id' => 575,
				'unit_id' => 236
			],
			[
				'id' => 4267,
				'card_id' => 2055,
				'unit_id' => 236
			],
			[
				'id' => 4268,
				'card_id' => 2049,
				'unit_id' => 236
			],
			[
				'id' => 4269,
				'card_id' => 544,
				'unit_id' => 236
			],
			[
				'id' => 4270,
				'card_id' => 2057,
				'unit_id' => 236
			],
			[
				'id' => 4271,
				'card_id' => 2058,
				'unit_id' => 236
			],
			[
				'id' => 4272,
				'card_id' => 2059,
				'unit_id' => 236
			],
			[
				'id' => 4273,
				'card_id' => 2068,
				'unit_id' => 236
			],
			[
				'id' => 4274,
				'card_id' => 2069,
				'unit_id' => 236
			],
			[
				'id' => 4275,
				'card_id' => 2108,
				'unit_id' => 236
			],
			[
				'id' => 4276,
				'card_id' => 2109,
				'unit_id' => 236
			],
			[
				'id' => 4277,
				'card_id' => 2086,
				'unit_id' => 236
			],
			[
				'id' => 4278,
				'card_id' => 2087,
				'unit_id' => 236
			],
			[
				'id' => 4279,
				'card_id' => 2066,
				'unit_id' => 236
			],
			[
				'id' => 4280,
				'card_id' => 2067,
				'unit_id' => 236
			],
			[
				'id' => 4281,
				'card_id' => 2100,
				'unit_id' => 236
			],
			[
				'id' => 4282,
				'card_id' => 2101,
				'unit_id' => 236
			],
			[
				'id' => 4283,
				'card_id' => 2098,
				'unit_id' => 236
			],
			[
				'id' => 4284,
				'card_id' => 2099,
				'unit_id' => 236
			],
			[
				'id' => 4285,
				'card_id' => 453,
				'unit_id' => 237
			],
			[
				'id' => 4286,
				'card_id' => 575,
				'unit_id' => 237
			],
			[
				'id' => 4287,
				'card_id' => 2057,
				'unit_id' => 237
			],
			[
				'id' => 4288,
				'card_id' => 2053,
				'unit_id' => 237
			],
			[
				'id' => 4289,
				'card_id' => 2055,
				'unit_id' => 237
			],
			[
				'id' => 4290,
				'card_id' => 2059,
				'unit_id' => 237
			],
			[
				'id' => 4291,
				'card_id' => 2051,
				'unit_id' => 237
			],
			[
				'id' => 4292,
				'card_id' => 2123,
				'unit_id' => 237
			],
			[
				'id' => 4293,
				'card_id' => 2049,
				'unit_id' => 237
			],
			[
				'id' => 4294,
				'card_id' => 2052,
				'unit_id' => 237
			],
			[
				'id' => 4295,
				'card_id' => 544,
				'unit_id' => 237
			],
			[
				'id' => 4296,
				'card_id' => 2127,
				'unit_id' => 237
			],
			[
				'id' => 4297,
				'card_id' => 2058,
				'unit_id' => 237
			],
			[
				'id' => 4298,
				'card_id' => 2129,
				'unit_id' => 238
			],
			[
				'id' => 4299,
				'card_id' => 2130,
				'unit_id' => 238
			],
			[
				'id' => 4300,
				'card_id' => 2131,
				'unit_id' => 238
			],
			[
				'id' => 4301,
				'card_id' => 2132,
				'unit_id' => 238
			],
			[
				'id' => 4302,
				'card_id' => 2151,
				'unit_id' => 238
			],
			[
				'id' => 4303,
				'card_id' => 2152,
				'unit_id' => 238
			],
			[
				'id' => 4304,
				'card_id' => 2135,
				'unit_id' => 238
			],
			[
				'id' => 4305,
				'card_id' => 1284,
				'unit_id' => 238
			],
			[
				'id' => 4306,
				'card_id' => 4227,
				'unit_id' => 238
			],
			[
				'id' => 4307,
				'card_id' => 2148,
				'unit_id' => 238
			],
			[
				'id' => 4308,
				'card_id' => 2150,
				'unit_id' => 238
			],
			[
				'id' => 4309,
				'card_id' => 4228,
				'unit_id' => 238
			],
			[
				'id' => 4310,
				'card_id' => 2157,
				'unit_id' => 238
			],
			[
				'id' => 4311,
				'card_id' => 2159,
				'unit_id' => 239
			],
			[
				'id' => 4312,
				'card_id' => 2160,
				'unit_id' => 239
			],
			[
				'id' => 4313,
				'card_id' => 2161,
				'unit_id' => 239
			],
			[
				'id' => 4314,
				'card_id' => 2162,
				'unit_id' => 239
			],
			[
				'id' => 4315,
				'card_id' => 2163,
				'unit_id' => 239
			],
			[
				'id' => 4316,
				'card_id' => 2164,
				'unit_id' => 239
			],
			[
				'id' => 4317,
				'card_id' => 2165,
				'unit_id' => 239
			],
			[
				'id' => 4318,
				'card_id' => 2166,
				'unit_id' => 239
			],
			[
				'id' => 4319,
				'card_id' => 2184,
				'unit_id' => 239
			],
			[
				'id' => 4320,
				'card_id' => 1881,
				'unit_id' => 239
			],
			[
				'id' => 4321,
				'card_id' => 2197,
				'unit_id' => 239
			],
			[
				'id' => 4322,
				'card_id' => 2182,
				'unit_id' => 239
			],
			[
				'id' => 4323,
				'card_id' => 2176,
				'unit_id' => 239
			],
			[
				'id' => 4324,
				'card_id' => 2213,
				'unit_id' => 239
			],
			[
				'id' => 4325,
				'card_id' => 1291,
				'unit_id' => 239
			],
			[
				'id' => 4326,
				'card_id' => 1432,
				'unit_id' => 239
			],
			[
				'id' => 4327,
				'card_id' => 2194,
				'unit_id' => 239
			],
			[
				'id' => 4328,
				'card_id' => 691,
				'unit_id' => 240
			],
			[
				'id' => 4329,
				'card_id' => 2252,
				'unit_id' => 240
			],
			[
				'id' => 4330,
				'card_id' => 2253,
				'unit_id' => 240
			],
			[
				'id' => 4331,
				'card_id' => 2255,
				'unit_id' => 240
			],
			[
				'id' => 4332,
				'card_id' => 2257,
				'unit_id' => 240
			],
			[
				'id' => 4333,
				'card_id' => 2254,
				'unit_id' => 240
			],
			[
				'id' => 4334,
				'card_id' => 2256,
				'unit_id' => 240
			],
			[
				'id' => 4335,
				'card_id' => 2258,
				'unit_id' => 240
			],
			[
				'id' => 4336,
				'card_id' => 2259,
				'unit_id' => 241
			],
			[
				'id' => 4337,
				'card_id' => 2260,
				'unit_id' => 241
			],
			[
				'id' => 4338,
				'card_id' => 2049,
				'unit_id' => 242
			],
			[
				'id' => 4339,
				'card_id' => 691,
				'unit_id' => 242
			],
			[
				'id' => 4340,
				'card_id' => 2287,
				'unit_id' => 242
			],
			[
				'id' => 4341,
				'card_id' => 2288,
				'unit_id' => 242
			],
			[
				'id' => 4342,
				'card_id' => 2292,
				'unit_id' => 242
			],
			[
				'id' => 4343,
				'card_id' => 2245,
				'unit_id' => 242
			],
			[
				'id' => 4344,
				'card_id' => 2289,
				'unit_id' => 242
			],
			[
				'id' => 4345,
				'card_id' => 2235,
				'unit_id' => 242
			],
			[
				'id' => 4346,
				'card_id' => 2293,
				'unit_id' => 242
			],
			[
				'id' => 4347,
				'card_id' => 2297,
				'unit_id' => 242
			],
			[
				'id' => 4348,
				'card_id' => 2305,
				'unit_id' => 242
			],
			[
				'id' => 4349,
				'card_id' => 2308,
				'unit_id' => 242
			],
			[
				'id' => 4350,
				'card_id' => 2343,
				'unit_id' => 243
			],
			[
				'id' => 4351,
				'card_id' => 4076,
				'unit_id' => 243
			],
			[
				'id' => 4352,
				'card_id' => 273,
				'unit_id' => 243
			],
			[
				'id' => 4353,
				'card_id' => 4081,
				'unit_id' => 243
			],
			[
				'id' => 4354,
				'card_id' => 221,
				'unit_id' => 243
			],
			[
				'id' => 4355,
				'card_id' => 218,
				'unit_id' => 243
			],
			[
				'id' => 4356,
				'card_id' => 2332,
				'unit_id' => 243
			],
			[
				'id' => 4357,
				'card_id' => 2356,
				'unit_id' => 243
			],
			[
				'id' => 4358,
				'card_id' => 1417,
				'unit_id' => 243
			],
			[
				'id' => 4359,
				'card_id' => 2322,
				'unit_id' => 243
			],
			[
				'id' => 4360,
				'card_id' => 848,
				'unit_id' => 244
			],
			[
				'id' => 4361,
				'card_id' => 981,
				'unit_id' => 244
			],
			[
				'id' => 4362,
				'card_id' => 2359,
				'unit_id' => 244
			],
			[
				'id' => 4363,
				'card_id' => 4231,
				'unit_id' => 244
			],
			[
				'id' => 4364,
				'card_id' => 2360,
				'unit_id' => 244
			],
			[
				'id' => 4365,
				'card_id' => 2361,
				'unit_id' => 244
			],
			[
				'id' => 4366,
				'card_id' => 4232,
				'unit_id' => 244
			],
			[
				'id' => 4367,
				'card_id' => 2362,
				'unit_id' => 244
			],
			[
				'id' => 4368,
				'card_id' => 2363,
				'unit_id' => 244
			],
			[
				'id' => 4369,
				'card_id' => 4233,
				'unit_id' => 244
			],
			[
				'id' => 4370,
				'card_id' => 2364,
				'unit_id' => 244
			],
			[
				'id' => 4371,
				'card_id' => 2365,
				'unit_id' => 244
			],
			[
				'id' => 4372,
				'card_id' => 2626,
				'unit_id' => 244
			],
			[
				'id' => 4373,
				'card_id' => 4234,
				'unit_id' => 244
			],
			[
				'id' => 4374,
				'card_id' => 4235,
				'unit_id' => 244
			],
			[
				'id' => 4375,
				'card_id' => 4236,
				'unit_id' => 244
			],
			[
				'id' => 4376,
				'card_id' => 4237,
				'unit_id' => 244
			],
			[
				'id' => 4377,
				'card_id' => 4238,
				'unit_id' => 244
			],
			[
				'id' => 4378,
				'card_id' => 4239,
				'unit_id' => 244
			],
			[
				'id' => 4379,
				'card_id' => 4240,
				'unit_id' => 244
			],
			[
				'id' => 4380,
				'card_id' => 981,
				'unit_id' => 245
			],
			[
				'id' => 4381,
				'card_id' => 2383,
				'unit_id' => 245
			],
			[
				'id' => 4382,
				'card_id' => 2360,
				'unit_id' => 245
			],
			[
				'id' => 4383,
				'card_id' => 2385,
				'unit_id' => 245
			],
			[
				'id' => 4384,
				'card_id' => 2362,
				'unit_id' => 245
			],
			[
				'id' => 4385,
				'card_id' => 2387,
				'unit_id' => 245
			],
			[
				'id' => 4386,
				'card_id' => 4234,
				'unit_id' => 245
			],
			[
				'id' => 4387,
				'card_id' => 4245,
				'unit_id' => 245
			],
			[
				'id' => 4388,
				'card_id' => 4241,
				'unit_id' => 245
			],
			[
				'id' => 4389,
				'card_id' => 4242,
				'unit_id' => 245
			],
			[
				'id' => 4390,
				'card_id' => 4243,
				'unit_id' => 245
			],
			[
				'id' => 4391,
				'card_id' => 4244,
				'unit_id' => 245
			],
			[
				'id' => 4392,
				'card_id' => 2354,
				'unit_id' => 246
			],
			[
				'id' => 4393,
				'card_id' => 2419,
				'unit_id' => 246
			],
			[
				'id' => 4394,
				'card_id' => 2420,
				'unit_id' => 246
			],
			[
				'id' => 4395,
				'card_id' => 2433,
				'unit_id' => 246
			],
			[
				'id' => 4396,
				'card_id' => 2426,
				'unit_id' => 246
			],
			[
				'id' => 4397,
				'card_id' => 2446,
				'unit_id' => 246
			],
			[
				'id' => 4398,
				'card_id' => 2438,
				'unit_id' => 246
			],
			[
				'id' => 4399,
				'card_id' => 2453,
				'unit_id' => 246
			],
			[
				'id' => 4400,
				'card_id' => 2422,
				'unit_id' => 246
			],
			[
				'id' => 4401,
				'card_id' => 4246,
				'unit_id' => 246
			],
			[
				'id' => 4402,
				'card_id' => 4247,
				'unit_id' => 246
			],
			[
				'id' => 4403,
				'card_id' => 4248,
				'unit_id' => 246
			],
			[
				'id' => 4404,
				'card_id' => 4250,
				'unit_id' => 246
			],
			[
				'id' => 4405,
				'card_id' => 4249,
				'unit_id' => 246
			],
			[
				'id' => 4406,
				'card_id' => 4251,
				'unit_id' => 246
			],
			[
				'id' => 4407,
				'card_id' => 2448,
				'unit_id' => 246
			],
			[
				'id' => 4408,
				'card_id' => 2427,
				'unit_id' => 246
			],
			[
				'id' => 4409,
				'card_id' => 2447,
				'unit_id' => 246
			],
			[
				'id' => 4410,
				'card_id' => 2436,
				'unit_id' => 246
			],
			[
				'id' => 4411,
				'card_id' => 2428,
				'unit_id' => 246
			],
			[
				'id' => 4412,
				'card_id' => 2435,
				'unit_id' => 246
			],
			[
				'id' => 4413,
				'card_id' => 2452,
				'unit_id' => 246
			],
			[
				'id' => 4414,
				'card_id' => 2457,
				'unit_id' => 247
			],
			[
				'id' => 4415,
				'card_id' => 2458,
				'unit_id' => 247
			],
			[
				'id' => 4416,
				'card_id' => 2459,
				'unit_id' => 247
			],
			[
				'id' => 4417,
				'card_id' => 2460,
				'unit_id' => 247
			],
			[
				'id' => 4418,
				'card_id' => 2461,
				'unit_id' => 247
			],
			[
				'id' => 4419,
				'card_id' => 2462,
				'unit_id' => 247
			],
			[
				'id' => 4420,
				'card_id' => 2464,
				'unit_id' => 247
			],
			[
				'id' => 4421,
				'card_id' => 2466,
				'unit_id' => 247
			],
			[
				'id' => 4422,
				'card_id' => 2467,
				'unit_id' => 247
			],
			[
				'id' => 4423,
				'card_id' => 2469,
				'unit_id' => 247
			],
			[
				'id' => 4424,
				'card_id' => 2471,
				'unit_id' => 247
			],
			[
				'id' => 4425,
				'card_id' => 2474,
				'unit_id' => 247
			],
			[
				'id' => 4426,
				'card_id' => 2475,
				'unit_id' => 247
			],
			[
				'id' => 4427,
				'card_id' => 2477,
				'unit_id' => 247
			],
			[
				'id' => 4428,
				'card_id' => 2480,
				'unit_id' => 247
			],
			[
				'id' => 4429,
				'card_id' => 2482,
				'unit_id' => 247
			],
			[
				'id' => 4430,
				'card_id' => 2491,
				'unit_id' => 248
			],
			[
				'id' => 4431,
				'card_id' => 2492,
				'unit_id' => 248
			],
			[
				'id' => 4432,
				'card_id' => 2505,
				'unit_id' => 248
			],
			[
				'id' => 4433,
				'card_id' => 2506,
				'unit_id' => 248
			],
			[
				'id' => 4434,
				'card_id' => 2513,
				'unit_id' => 248
			],
			[
				'id' => 4435,
				'card_id' => 2514,
				'unit_id' => 248
			],
			[
				'id' => 4436,
				'card_id' => 2517,
				'unit_id' => 248
			],
			[
				'id' => 4437,
				'card_id' => 2518,
				'unit_id' => 248
			],
			[
				'id' => 4438,
				'card_id' => 2499,
				'unit_id' => 248
			],
			[
				'id' => 4439,
				'card_id' => 2500,
				'unit_id' => 248
			],
			[
				'id' => 4440,
				'card_id' => 2527,
				'unit_id' => 249
			],
			[
				'id' => 4441,
				'card_id' => 2530,
				'unit_id' => 249
			],
			[
				'id' => 4442,
				'card_id' => 2532,
				'unit_id' => 249
			],
			[
				'id' => 4443,
				'card_id' => 2535,
				'unit_id' => 249
			],
			[
				'id' => 4444,
				'card_id' => 2539,
				'unit_id' => 249
			],
			[
				'id' => 4445,
				'card_id' => 2543,
				'unit_id' => 249
			],
			[
				'id' => 4446,
				'card_id' => 2547,
				'unit_id' => 249
			],
			[
				'id' => 4447,
				'card_id' => 2537,
				'unit_id' => 249
			],
			[
				'id' => 4448,
				'card_id' => 2545,
				'unit_id' => 249
			],
			[
				'id' => 4449,
				'card_id' => 56,
				'unit_id' => 249
			],
			[
				'id' => 4450,
				'card_id' => 2540,
				'unit_id' => 249
			],
			[
				'id' => 4451,
				'card_id' => 4254,
				'unit_id' => 249
			],
			[
				'id' => 4452,
				'card_id' => 2533,
				'unit_id' => 249
			],
			[
				'id' => 4453,
				'card_id' => 2526,
				'unit_id' => 249
			],
			[
				'id' => 4454,
				'card_id' => 2550,
				'unit_id' => 256
			],
			[
				'id' => 4455,
				'card_id' => 2551,
				'unit_id' => 256
			],
			[
				'id' => 4456,
				'card_id' => 2552,
				'unit_id' => 256
			],
			[
				'id' => 4457,
				'card_id' => 2553,
				'unit_id' => 256
			],
			[
				'id' => 4458,
				'card_id' => 2554,
				'unit_id' => 256
			],
			[
				'id' => 4459,
				'card_id' => 2555,
				'unit_id' => 256
			],
			[
				'id' => 4460,
				'card_id' => 2556,
				'unit_id' => 256
			],
			[
				'id' => 4461,
				'card_id' => 2557,
				'unit_id' => 256
			],
			[
				'id' => 4462,
				'card_id' => 2558,
				'unit_id' => 256
			],
			[
				'id' => 4463,
				'card_id' => 2559,
				'unit_id' => 256
			],
			[
				'id' => 4464,
				'card_id' => 2560,
				'unit_id' => 256
			],
			[
				'id' => 4465,
				'card_id' => 2561,
				'unit_id' => 256
			],
			[
				'id' => 4466,
				'card_id' => 2562,
				'unit_id' => 256
			],
			[
				'id' => 4467,
				'card_id' => 2563,
				'unit_id' => 256
			],
			[
				'id' => 4468,
				'card_id' => 1644,
				'unit_id' => 256
			],
			[
				'id' => 4469,
				'card_id' => 2565,
				'unit_id' => 256
			],
			[
				'id' => 4470,
				'card_id' => 2566,
				'unit_id' => 256
			],
			[
				'id' => 4471,
				'card_id' => 2567,
				'unit_id' => 256
			],
			[
				'id' => 4472,
				'card_id' => 2568,
				'unit_id' => 256
			],
			[
				'id' => 4473,
				'card_id' => 2569,
				'unit_id' => 256
			],
			[
				'id' => 4474,
				'card_id' => 2570,
				'unit_id' => 256
			],
			[
				'id' => 4475,
				'card_id' => 2571,
				'unit_id' => 256
			],
			[
				'id' => 4476,
				'card_id' => 2572,
				'unit_id' => 256
			],
			[
				'id' => 4477,
				'card_id' => 2573,
				'unit_id' => 256
			],
			[
				'id' => 4478,
				'card_id' => 2574,
				'unit_id' => 256
			],
			[
				'id' => 4479,
				'card_id' => 2575,
				'unit_id' => 256
			],
			[
				'id' => 4480,
				'card_id' => 2576,
				'unit_id' => 256
			],
			[
				'id' => 4481,
				'card_id' => 2577,
				'unit_id' => 256
			],
			[
				'id' => 4482,
				'card_id' => 2578,
				'unit_id' => 256
			],
			[
				'id' => 4483,
				'card_id' => 2579,
				'unit_id' => 256
			],
			[
				'id' => 4484,
				'card_id' => 2580,
				'unit_id' => 256
			],
			[
				'id' => 4485,
				'card_id' => 2581,
				'unit_id' => 256
			],
			[
				'id' => 4486,
				'card_id' => 2582,
				'unit_id' => 256
			],
			[
				'id' => 4487,
				'card_id' => 2583,
				'unit_id' => 256
			],
			[
				'id' => 4488,
				'card_id' => 4348,
				'unit_id' => 256
			],
			[
				'id' => 4489,
				'card_id' => 4349,
				'unit_id' => 256
			],
			[
				'id' => 4490,
				'card_id' => 4350,
				'unit_id' => 256
			],
			[
				'id' => 4491,
				'card_id' => 4351,
				'unit_id' => 256
			],
			[
				'id' => 4492,
				'card_id' => 4352,
				'unit_id' => 256
			],
			[
				'id' => 4493,
				'card_id' => 4353,
				'unit_id' => 256
			],
			[
				'id' => 4494,
				'card_id' => 4354,
				'unit_id' => 256
			],
			[
				'id' => 4495,
				'card_id' => 4355,
				'unit_id' => 256
			],
			[
				'id' => 4504,
				'card_id' => 264,
				'unit_id' => 258
			],
			[
				'id' => 4505,
				'card_id' => 2597,
				'unit_id' => 258
			],
			[
				'id' => 4506,
				'card_id' => 2598,
				'unit_id' => 258
			],
			[
				'id' => 4507,
				'card_id' => 2599,
				'unit_id' => 258
			],
			[
				'id' => 4508,
				'card_id' => 2600,
				'unit_id' => 258
			],
			[
				'id' => 4509,
				'card_id' => 2601,
				'unit_id' => 258
			],
			[
				'id' => 4510,
				'card_id' => 2602,
				'unit_id' => 258
			],
			[
				'id' => 4511,
				'card_id' => 2603,
				'unit_id' => 258
			],
			[
				'id' => 4512,
				'card_id' => 2604,
				'unit_id' => 258
			],
			[
				'id' => 4513,
				'card_id' => 2605,
				'unit_id' => 258
			],
			[
				'id' => 4514,
				'card_id' => 1654,
				'unit_id' => 258
			],
			[
				'id' => 4515,
				'card_id' => 2607,
				'unit_id' => 258
			],
			[
				'id' => 4516,
				'card_id' => 2608,
				'unit_id' => 258
			],
			[
				'id' => 4517,
				'card_id' => 2609,
				'unit_id' => 258
			],
			[
				'id' => 4518,
				'card_id' => 2610,
				'unit_id' => 258
			],
			[
				'id' => 4519,
				'card_id' => 2611,
				'unit_id' => 258
			],
			[
				'id' => 4520,
				'card_id' => 4076,
				'unit_id' => 258
			],
			[
				'id' => 4521,
				'card_id' => 2613,
				'unit_id' => 258
			],
			[
				'id' => 4522,
				'card_id' => 2614,
				'unit_id' => 258
			],
			[
				'id' => 4523,
				'card_id' => 2615,
				'unit_id' => 258
			],
			[
				'id' => 4524,
				'card_id' => 564,
				'unit_id' => 258
			],
			[
				'id' => 4525,
				'card_id' => 2617,
				'unit_id' => 258
			],
			[
				'id' => 4526,
				'card_id' => 2618,
				'unit_id' => 258
			],
			[
				'id' => 4527,
				'card_id' => 2619,
				'unit_id' => 258
			],
			[
				'id' => 4528,
				'card_id' => 2620,
				'unit_id' => 258
			],
			[
				'id' => 4529,
				'card_id' => 2621,
				'unit_id' => 258
			],
			[
				'id' => 4530,
				'card_id' => 2622,
				'unit_id' => 258
			],
			[
				'id' => 4531,
				'card_id' => 2623,
				'unit_id' => 258
			],
			[
				'id' => 4532,
				'card_id' => 234,
				'unit_id' => 258
			],
			[
				'id' => 4533,
				'card_id' => 2625,
				'unit_id' => 258
			],
			[
				'id' => 4534,
				'card_id' => 4364,
				'unit_id' => 258
			],
			[
				'id' => 4535,
				'card_id' => 2627,
				'unit_id' => 258
			],
			[
				'id' => 4536,
				'card_id' => 4081,
				'unit_id' => 258
			],
			[
				'id' => 4537,
				'card_id' => 2629,
				'unit_id' => 258
			],
			[
				'id' => 4538,
				'card_id' => 2630,
				'unit_id' => 258
			],
			[
				'id' => 4539,
				'card_id' => 2631,
				'unit_id' => 258
			],
			[
				'id' => 4540,
				'card_id' => 4356,
				'unit_id' => 258
			],
			[
				'id' => 4541,
				'card_id' => 4357,
				'unit_id' => 258
			],
			[
				'id' => 4542,
				'card_id' => 4358,
				'unit_id' => 258
			],
			[
				'id' => 4543,
				'card_id' => 4359,
				'unit_id' => 258
			],
			[
				'id' => 4544,
				'card_id' => 4360,
				'unit_id' => 258
			],
			[
				'id' => 4545,
				'card_id' => 4361,
				'unit_id' => 258
			],
			[
				'id' => 4546,
				'card_id' => 4362,
				'unit_id' => 258
			],
			[
				'id' => 4547,
				'card_id' => 4363,
				'unit_id' => 258
			],
			[
				'id' => 4556,
				'card_id' => 453,
				'unit_id' => 260
			],
			[
				'id' => 4557,
				'card_id' => 2051,
				'unit_id' => 260
			],
			[
				'id' => 4558,
				'card_id' => 2052,
				'unit_id' => 260
			],
			[
				'id' => 4559,
				'card_id' => 2647,
				'unit_id' => 260
			],
			[
				'id' => 4560,
				'card_id' => 2127,
				'unit_id' => 260
			],
			[
				'id' => 4561,
				'card_id' => 2681,
				'unit_id' => 260
			],
			[
				'id' => 4562,
				'card_id' => 2679,
				'unit_id' => 260
			],
			[
				'id' => 4563,
				'card_id' => 2680,
				'unit_id' => 260
			],
			[
				'id' => 4564,
				'card_id' => 2682,
				'unit_id' => 260
			],
			[
				'id' => 4565,
				'card_id' => 2683,
				'unit_id' => 260
			],
			[
				'id' => 4566,
				'card_id' => 2706,
				'unit_id' => 260
			],
			[
				'id' => 4567,
				'card_id' => 2582,
				'unit_id' => 260
			],
			[
				'id' => 4568,
				'card_id' => 2705,
				'unit_id' => 260
			],
			[
				'id' => 4569,
				'card_id' => 2583,
				'unit_id' => 260
			],
			[
				'id' => 4570,
				'card_id' => 2708,
				'unit_id' => 260
			],
			[
				'id' => 4571,
				'card_id' => 1080,
				'unit_id' => 261
			],
			[
				'id' => 4572,
				'card_id' => 2720,
				'unit_id' => 261
			],
			[
				'id' => 4573,
				'card_id' => 2721,
				'unit_id' => 261
			],
			[
				'id' => 4574,
				'card_id' => 1221,
				'unit_id' => 261
			],
			[
				'id' => 4575,
				'card_id' => 2723,
				'unit_id' => 261
			],
			[
				'id' => 4576,
				'card_id' => 2724,
				'unit_id' => 261
			],
			[
				'id' => 4577,
				'card_id' => 1081,
				'unit_id' => 261
			],
			[
				'id' => 4578,
				'card_id' => 2726,
				'unit_id' => 261
			],
			[
				'id' => 4579,
				'card_id' => 2727,
				'unit_id' => 261
			],
			[
				'id' => 4580,
				'card_id' => 1732,
				'unit_id' => 261
			],
			[
				'id' => 4581,
				'card_id' => 2729,
				'unit_id' => 261
			],
			[
				'id' => 4582,
				'card_id' => 2730,
				'unit_id' => 261
			],
			[
				'id' => 4583,
				'card_id' => 1336,
				'unit_id' => 261
			],
			[
				'id' => 4584,
				'card_id' => 2732,
				'unit_id' => 261
			],
			[
				'id' => 4585,
				'card_id' => 2733,
				'unit_id' => 261
			],
			[
				'id' => 4586,
				'card_id' => 1807,
				'unit_id' => 261
			],
			[
				'id' => 4587,
				'card_id' => 2735,
				'unit_id' => 261
			],
			[
				'id' => 4588,
				'card_id' => 2736,
				'unit_id' => 261
			],
			[
				'id' => 4589,
				'card_id' => 4368,
				'unit_id' => 261
			],
			[
				'id' => 4590,
				'card_id' => 4370,
				'unit_id' => 261
			],
			[
				'id' => 4591,
				'card_id' => 4367,
				'unit_id' => 261
			],
			[
				'id' => 4592,
				'card_id' => 4371,
				'unit_id' => 261
			],
			[
				'id' => 4593,
				'card_id' => 4372,
				'unit_id' => 261
			],
			[
				'id' => 4594,
				'card_id' => 4369,
				'unit_id' => 261
			],
			[
				'id' => 4595,
				'card_id' => 993,
				'unit_id' => 261
			],
			[
				'id' => 4596,
				'card_id' => 2738,
				'unit_id' => 261
			],
			[
				'id' => 4597,
				'card_id' => 4146,
				'unit_id' => 261
			],
			[
				'id' => 4598,
				'card_id' => 2740,
				'unit_id' => 261
			],
			[
				'id' => 4599,
				'card_id' => 981,
				'unit_id' => 261
			],
			[
				'id' => 4600,
				'card_id' => 2746,
				'unit_id' => 261
			],
			[
				'id' => 4601,
				'card_id' => 2747,
				'unit_id' => 261
			],
			[
				'id' => 4602,
				'card_id' => 2748,
				'unit_id' => 261
			],
			[
				'id' => 4603,
				'card_id' => 996,
				'unit_id' => 261
			],
			[
				'id' => 4604,
				'card_id' => 2750,
				'unit_id' => 261
			],
			[
				'id' => 4605,
				'card_id' => 1829,
				'unit_id' => 261
			],
			[
				'id' => 4606,
				'card_id' => 2752,
				'unit_id' => 261
			],
			[
				'id' => 4607,
				'card_id' => 2362,
				'unit_id' => 261
			],
			[
				'id' => 4608,
				'card_id' => 2754,
				'unit_id' => 261
			],
			[
				'id' => 4609,
				'card_id' => 4373,
				'unit_id' => 261
			],
			[
				'id' => 4610,
				'card_id' => 4374,
				'unit_id' => 261
			],
			[
				'id' => 4611,
				'card_id' => 4375,
				'unit_id' => 261
			],
			[
				'id' => 4612,
				'card_id' => 4376,
				'unit_id' => 261
			],
			[
				'id' => 4613,
				'card_id' => 4377,
				'unit_id' => 261
			],
			[
				'id' => 4614,
				'card_id' => 4378,
				'unit_id' => 261
			],
			[
				'id' => 4615,
				'card_id' => 4379,
				'unit_id' => 261
			],
			[
				'id' => 4616,
				'card_id' => 2759,
				'unit_id' => 262
			],
			[
				'id' => 4617,
				'card_id' => 2760,
				'unit_id' => 262
			],
			[
				'id' => 4618,
				'card_id' => 2761,
				'unit_id' => 262
			],
			[
				'id' => 4619,
				'card_id' => 2762,
				'unit_id' => 262
			],
			[
				'id' => 4620,
				'card_id' => 2763,
				'unit_id' => 262
			],
			[
				'id' => 4621,
				'card_id' => 2764,
				'unit_id' => 262
			],
			[
				'id' => 4622,
				'card_id' => 4380,
				'unit_id' => 262
			],
			[
				'id' => 4623,
				'card_id' => 2740,
				'unit_id' => 262
			],
			[
				'id' => 4624,
				'card_id' => 4381,
				'unit_id' => 262
			],
			[
				'id' => 4625,
				'card_id' => 2724,
				'unit_id' => 262
			],
			[
				'id' => 4626,
				'card_id' => 4382,
				'unit_id' => 262
			],
			[
				'id' => 4627,
				'card_id' => 2736,
				'unit_id' => 262
			],
			[
				'id' => 4628,
				'card_id' => 4383,
				'unit_id' => 262
			],
			[
				'id' => 4629,
				'card_id' => 2746,
				'unit_id' => 262
			],
			[
				'id' => 4630,
				'card_id' => 4384,
				'unit_id' => 262
			],
			[
				'id' => 4631,
				'card_id' => 2730,
				'unit_id' => 262
			],
			[
				'id' => 4632,
				'card_id' => 4385,
				'unit_id' => 262
			],
			[
				'id' => 4633,
				'card_id' => 2721,
				'unit_id' => 262
			],
			[
				'id' => 4634,
				'card_id' => 4386,
				'unit_id' => 262
			],
			[
				'id' => 4635,
				'card_id' => 2754,
				'unit_id' => 262
			],
			[
				'id' => 4636,
				'card_id' => 4395,
				'unit_id' => 262
			],
			[
				'id' => 4637,
				'card_id' => 4396,
				'unit_id' => 262
			],
			[
				'id' => 4638,
				'card_id' => 4411,
				'unit_id' => 262
			],
			[
				'id' => 4639,
				'card_id' => 4398,
				'unit_id' => 262
			],
			[
				'id' => 4640,
				'card_id' => 4399,
				'unit_id' => 262
			],
			[
				'id' => 4641,
				'card_id' => 4400,
				'unit_id' => 262
			],
			[
				'id' => 4642,
				'card_id' => 4401,
				'unit_id' => 262
			],
			[
				'id' => 4643,
				'card_id' => 4402,
				'unit_id' => 262
			],
			[
				'id' => 4644,
				'card_id' => 4403,
				'unit_id' => 262
			],
			[
				'id' => 4645,
				'card_id' => 4404,
				'unit_id' => 262
			],
			[
				'id' => 4646,
				'card_id' => 1450,
				'unit_id' => 263
			],
			[
				'id' => 4647,
				'card_id' => 2765,
				'unit_id' => 263
			],
			[
				'id' => 4648,
				'card_id' => 2787,
				'unit_id' => 263
			],
			[
				'id' => 4649,
				'card_id' => 2767,
				'unit_id' => 263
			],
			[
				'id' => 4650,
				'card_id' => 1285,
				'unit_id' => 263
			],
			[
				'id' => 4651,
				'card_id' => 2790,
				'unit_id' => 263
			],
			[
				'id' => 4652,
				'card_id' => 1883,
				'unit_id' => 263
			],
			[
				'id' => 4653,
				'card_id' => 2769,
				'unit_id' => 263
			],
			[
				'id' => 4654,
				'card_id' => 1456,
				'unit_id' => 263
			],
			[
				'id' => 4655,
				'card_id' => 2794,
				'unit_id' => 263
			],
			[
				'id' => 4656,
				'card_id' => 2795,
				'unit_id' => 263
			],
			[
				'id' => 4657,
				'card_id' => 2796,
				'unit_id' => 263
			],
			[
				'id' => 4658,
				'card_id' => 1444,
				'unit_id' => 263
			],
			[
				'id' => 4659,
				'card_id' => 2798,
				'unit_id' => 263
			],
			[
				'id' => 4660,
				'card_id' => 2799,
				'unit_id' => 263
			],
			[
				'id' => 4661,
				'card_id' => 2800,
				'unit_id' => 263
			],
			[
				'id' => 4662,
				'card_id' => 2801,
				'unit_id' => 263
			],
			[
				'id' => 4663,
				'card_id' => 2802,
				'unit_id' => 263
			],
			[
				'id' => 4664,
				'card_id' => 2193,
				'unit_id' => 263
			],
			[
				'id' => 4665,
				'card_id' => 2804,
				'unit_id' => 263
			],
			[
				'id' => 4666,
				'card_id' => 2805,
				'unit_id' => 263
			],
			[
				'id' => 4667,
				'card_id' => 2806,
				'unit_id' => 263
			],
			[
				'id' => 4668,
				'card_id' => 2807,
				'unit_id' => 263
			],
			[
				'id' => 4669,
				'card_id' => 2808,
				'unit_id' => 263
			],
			[
				'id' => 4670,
				'card_id' => 2809,
				'unit_id' => 263
			],
			[
				'id' => 4671,
				'card_id' => 2810,
				'unit_id' => 263
			],
			[
				'id' => 4672,
				'card_id' => 2811,
				'unit_id' => 263
			],
			[
				'id' => 4673,
				'card_id' => 2812,
				'unit_id' => 263
			],
			[
				'id' => 4674,
				'card_id' => 4405,
				'unit_id' => 263
			],
			[
				'id' => 4675,
				'card_id' => 4397,
				'unit_id' => 263
			],
			[
				'id' => 4676,
				'card_id' => 4407,
				'unit_id' => 263
			],
			[
				'id' => 4677,
				'card_id' => 4408,
				'unit_id' => 263
			],
			[
				'id' => 4678,
				'card_id' => 4409,
				'unit_id' => 263
			],
			[
				'id' => 4679,
				'card_id' => 4410,
				'unit_id' => 263
			],
			[
				'id' => 4680,
				'card_id' => 1450,
				'unit_id' => 264
			],
			[
				'id' => 4681,
				'card_id' => 1451,
				'unit_id' => 264
			],
			[
				'id' => 4682,
				'card_id' => 2765,
				'unit_id' => 264
			],
			[
				'id' => 4683,
				'card_id' => 2766,
				'unit_id' => 264
			],
			[
				'id' => 4684,
				'card_id' => 2787,
				'unit_id' => 264
			],
			[
				'id' => 4685,
				'card_id' => 1294,
				'unit_id' => 264
			],
			[
				'id' => 4686,
				'card_id' => 2767,
				'unit_id' => 264
			],
			[
				'id' => 4687,
				'card_id' => 2768,
				'unit_id' => 264
			],
			[
				'id' => 4688,
				'card_id' => 1285,
				'unit_id' => 264
			],
			[
				'id' => 4689,
				'card_id' => 1286,
				'unit_id' => 264
			],
			[
				'id' => 4690,
				'card_id' => 2790,
				'unit_id' => 264
			],
			[
				'id' => 4691,
				'card_id' => 2824,
				'unit_id' => 264
			],
			[
				'id' => 4692,
				'card_id' => 1883,
				'unit_id' => 264
			],
			[
				'id' => 4693,
				'card_id' => 2826,
				'unit_id' => 264
			],
			[
				'id' => 4694,
				'card_id' => 2769,
				'unit_id' => 264
			],
			[
				'id' => 4695,
				'card_id' => 2770,
				'unit_id' => 264
			],
			[
				'id' => 4696,
				'card_id' => 1456,
				'unit_id' => 264
			],
			[
				'id' => 4697,
				'card_id' => 1457,
				'unit_id' => 264
			],
			[
				'id' => 4698,
				'card_id' => 2794,
				'unit_id' => 264
			],
			[
				'id' => 4699,
				'card_id' => 2832,
				'unit_id' => 264
			],
			[
				'id' => 4700,
				'card_id' => 1444,
				'unit_id' => 264
			],
			[
				'id' => 4701,
				'card_id' => 1445,
				'unit_id' => 264
			],
			[
				'id' => 4702,
				'card_id' => 2798,
				'unit_id' => 264
			],
			[
				'id' => 4703,
				'card_id' => 2836,
				'unit_id' => 264
			],
			[
				'id' => 4704,
				'card_id' => 2801,
				'unit_id' => 264
			],
			[
				'id' => 4705,
				'card_id' => 2838,
				'unit_id' => 264
			],
			[
				'id' => 4706,
				'card_id' => 2802,
				'unit_id' => 264
			],
			[
				'id' => 4707,
				'card_id' => 2840,
				'unit_id' => 264
			],
			[
				'id' => 4708,
				'card_id' => 2193,
				'unit_id' => 264
			],
			[
				'id' => 4709,
				'card_id' => 2194,
				'unit_id' => 264
			],
			[
				'id' => 4710,
				'card_id' => 2804,
				'unit_id' => 264
			],
			[
				'id' => 4711,
				'card_id' => 2844,
				'unit_id' => 264
			],
			[
				'id' => 4712,
				'card_id' => 2807,
				'unit_id' => 264
			],
			[
				'id' => 4713,
				'card_id' => 1698,
				'unit_id' => 264
			],
			[
				'id' => 4714,
				'card_id' => 2808,
				'unit_id' => 264
			],
			[
				'id' => 4715,
				'card_id' => 2848,
				'unit_id' => 264
			],
			[
				'id' => 4716,
				'card_id' => 2809,
				'unit_id' => 264
			],
			[
				'id' => 4717,
				'card_id' => 2850,
				'unit_id' => 264
			],
			[
				'id' => 4718,
				'card_id' => 2810,
				'unit_id' => 264
			],
			[
				'id' => 4719,
				'card_id' => 2852,
				'unit_id' => 264
			],
			[
				'id' => 4720,
				'card_id' => 2811,
				'unit_id' => 264
			],
			[
				'id' => 4721,
				'card_id' => 2854,
				'unit_id' => 264
			],
			[
				'id' => 4722,
				'card_id' => 2812,
				'unit_id' => 264
			],
			[
				'id' => 4723,
				'card_id' => 2856,
				'unit_id' => 264
			],
			[
				'id' => 4724,
				'card_id' => 4411,
				'unit_id' => 264
			],
			[
				'id' => 4725,
				'card_id' => 4412,
				'unit_id' => 264
			],
			[
				'id' => 4726,
				'card_id' => 4413,
				'unit_id' => 264
			],
			[
				'id' => 4727,
				'card_id' => 4414,
				'unit_id' => 264
			],
			[
				'id' => 4728,
				'card_id' => 4415,
				'unit_id' => 264
			],
			[
				'id' => 4729,
				'card_id' => 4416,
				'unit_id' => 264
			],
			[
				'id' => 4730,
				'card_id' => 4417,
				'unit_id' => 264
			],
			[
				'id' => 4731,
				'card_id' => 4418,
				'unit_id' => 264
			],
			[
				'id' => 4732,
				'card_id' => 4419,
				'unit_id' => 264
			],
			[
				'id' => 4733,
				'card_id' => 4420,
				'unit_id' => 264
			],
			[
				'id' => 4734,
				'card_id' => 4396,
				'unit_id' => 264
			],
			[
				'id' => 4735,
				'card_id' => 250,
				'unit_id' => 265
			],
			[
				'id' => 4736,
				'card_id' => 2858,
				'unit_id' => 265
			],
			[
				'id' => 4737,
				'card_id' => 2859,
				'unit_id' => 265
			],
			[
				'id' => 4738,
				'card_id' => 2860,
				'unit_id' => 265
			],
			[
				'id' => 4739,
				'card_id' => 196,
				'unit_id' => 265
			],
			[
				'id' => 4740,
				'card_id' => 187,
				'unit_id' => 265
			],
			[
				'id' => 4741,
				'card_id' => 186,
				'unit_id' => 265
			],
			[
				'id' => 4742,
				'card_id' => 2864,
				'unit_id' => 265
			],
			[
				'id' => 4743,
				'card_id' => 2865,
				'unit_id' => 265
			],
			[
				'id' => 4744,
				'card_id' => 2866,
				'unit_id' => 265
			],
			[
				'id' => 4745,
				'card_id' => 2870,
				'unit_id' => 265
			],
			[
				'id' => 4746,
				'card_id' => 2871,
				'unit_id' => 265
			],
			[
				'id' => 4747,
				'card_id' => 2876,
				'unit_id' => 265
			],
			[
				'id' => 4748,
				'card_id' => 2869,
				'unit_id' => 265
			],
			[
				'id' => 4749,
				'card_id' => 2868,
				'unit_id' => 265
			],
			[
				'id' => 4750,
				'card_id' => 2874,
				'unit_id' => 265
			],
			[
				'id' => 4751,
				'card_id' => 2867,
				'unit_id' => 265
			],
			[
				'id' => 4752,
				'card_id' => 2872,
				'unit_id' => 265
			],
			[
				'id' => 4753,
				'card_id' => 2875,
				'unit_id' => 265
			],
			[
				'id' => 4754,
				'card_id' => 2873,
				'unit_id' => 265
			],
			[
				'id' => 4755,
				'card_id' => 2877,
				'unit_id' => 266
			],
			[
				'id' => 4756,
				'card_id' => 2878,
				'unit_id' => 266
			],
			[
				'id' => 4757,
				'card_id' => 2879,
				'unit_id' => 266
			],
			[
				'id' => 4758,
				'card_id' => 2880,
				'unit_id' => 266
			],
			[
				'id' => 4759,
				'card_id' => 2881,
				'unit_id' => 266
			],
			[
				'id' => 4760,
				'card_id' => 2882,
				'unit_id' => 266
			],
			[
				'id' => 4761,
				'card_id' => 2883,
				'unit_id' => 266
			],
			[
				'id' => 4762,
				'card_id' => 2884,
				'unit_id' => 266
			],
			[
				'id' => 4763,
				'card_id' => 2885,
				'unit_id' => 266
			],
			[
				'id' => 4764,
				'card_id' => 2886,
				'unit_id' => 266
			],
			[
				'id' => 4765,
				'card_id' => 2891,
				'unit_id' => 266
			],
			[
				'id' => 4766,
				'card_id' => 2890,
				'unit_id' => 266
			],
			[
				'id' => 4767,
				'card_id' => 2892,
				'unit_id' => 266
			],
			[
				'id' => 4768,
				'card_id' => 2887,
				'unit_id' => 266
			],
			[
				'id' => 4769,
				'card_id' => 2894,
				'unit_id' => 266
			],
			[
				'id' => 4770,
				'card_id' => 2889,
				'unit_id' => 266
			],
			[
				'id' => 4771,
				'card_id' => 2895,
				'unit_id' => 266
			],
			[
				'id' => 4772,
				'card_id' => 2896,
				'unit_id' => 266
			],
			[
				'id' => 4773,
				'card_id' => 2893,
				'unit_id' => 266
			],
			[
				'id' => 4774,
				'card_id' => 2888,
				'unit_id' => 266
			],
			[
				'id' => 4775,
				'card_id' => 2897,
				'unit_id' => 267
			],
			[
				'id' => 4776,
				'card_id' => 2898,
				'unit_id' => 267
			],
			[
				'id' => 4777,
				'card_id' => 2899,
				'unit_id' => 267
			],
			[
				'id' => 4778,
				'card_id' => 2900,
				'unit_id' => 267
			],
			[
				'id' => 4779,
				'card_id' => 2901,
				'unit_id' => 267
			],
			[
				'id' => 4780,
				'card_id' => 2902,
				'unit_id' => 267
			],
			[
				'id' => 4781,
				'card_id' => 2903,
				'unit_id' => 267
			],
			[
				'id' => 4782,
				'card_id' => 2904,
				'unit_id' => 267
			],
			[
				'id' => 4783,
				'card_id' => 2905,
				'unit_id' => 267
			],
			[
				'id' => 4784,
				'card_id' => 2906,
				'unit_id' => 267
			],
			[
				'id' => 4785,
				'card_id' => 2907,
				'unit_id' => 267
			],
			[
				'id' => 4786,
				'card_id' => 2908,
				'unit_id' => 267
			],
			[
				'id' => 4787,
				'card_id' => 2909,
				'unit_id' => 267
			],
			[
				'id' => 4788,
				'card_id' => 2910,
				'unit_id' => 267
			],
			[
				'id' => 4789,
				'card_id' => 2911,
				'unit_id' => 267
			],
			[
				'id' => 4790,
				'card_id' => 2912,
				'unit_id' => 267
			],
			[
				'id' => 4791,
				'card_id' => 2913,
				'unit_id' => 267
			],
			[
				'id' => 4792,
				'card_id' => 2914,
				'unit_id' => 267
			],
			[
				'id' => 4793,
				'card_id' => 2915,
				'unit_id' => 267
			],
			[
				'id' => 4794,
				'card_id' => 2916,
				'unit_id' => 267
			],
			[
				'id' => 4795,
				'card_id' => 2917,
				'unit_id' => 267
			],
			[
				'id' => 4796,
				'card_id' => 2918,
				'unit_id' => 267
			],
			[
				'id' => 4797,
				'card_id' => 2919,
				'unit_id' => 267
			],
			[
				'id' => 4798,
				'card_id' => 2920,
				'unit_id' => 267
			],
			[
				'id' => 4799,
				'card_id' => 4422,
				'unit_id' => 267
			],
			[
				'id' => 4800,
				'card_id' => 4423,
				'unit_id' => 267
			],
			[
				'id' => 4801,
				'card_id' => 4424,
				'unit_id' => 267
			],
			[
				'id' => 4802,
				'card_id' => 4425,
				'unit_id' => 267
			],
			[
				'id' => 4803,
				'card_id' => 4426,
				'unit_id' => 267
			],
			[
				'id' => 4804,
				'card_id' => 4427,
				'unit_id' => 267
			],
			[
				'id' => 4805,
				'card_id' => 4428,
				'unit_id' => 267
			],
			[
				'id' => 4806,
				'card_id' => 4429,
				'unit_id' => 267
			],
			[
				'id' => 4807,
				'card_id' => 4430,
				'unit_id' => 267
			],
			[
				'id' => 4808,
				'card_id' => 4431,
				'unit_id' => 267
			],
			[
				'id' => 4809,
				'card_id' => 4432,
				'unit_id' => 267
			],
			[
				'id' => 4810,
				'card_id' => 4433,
				'unit_id' => 267
			],
			[
				'id' => 4811,
				'card_id' => 2877,
				'unit_id' => 268
			],
			[
				'id' => 4812,
				'card_id' => 2942,
				'unit_id' => 268
			],
			[
				'id' => 4813,
				'card_id' => 2943,
				'unit_id' => 268
			],
			[
				'id' => 4814,
				'card_id' => 2944,
				'unit_id' => 268
			],
			[
				'id' => 4815,
				'card_id' => 2859,
				'unit_id' => 268
			],
			[
				'id' => 4816,
				'card_id' => 2946,
				'unit_id' => 268
			],
			[
				'id' => 4817,
				'card_id' => 2947,
				'unit_id' => 268
			],
			[
				'id' => 4818,
				'card_id' => 2948,
				'unit_id' => 268
			],
			[
				'id' => 4819,
				'card_id' => 196,
				'unit_id' => 268
			],
			[
				'id' => 4820,
				'card_id' => 2950,
				'unit_id' => 268
			],
			[
				'id' => 4821,
				'card_id' => 187,
				'unit_id' => 268
			],
			[
				'id' => 4822,
				'card_id' => 2952,
				'unit_id' => 268
			],
			[
				'id' => 4823,
				'card_id' => 186,
				'unit_id' => 268
			],
			[
				'id' => 4824,
				'card_id' => 2954,
				'unit_id' => 268
			],
			[
				'id' => 4825,
				'card_id' => 2864,
				'unit_id' => 268
			],
			[
				'id' => 4826,
				'card_id' => 2956,
				'unit_id' => 268
			],
			[
				'id' => 4827,
				'card_id' => 2865,
				'unit_id' => 268
			],
			[
				'id' => 4828,
				'card_id' => 2958,
				'unit_id' => 268
			],
			[
				'id' => 4829,
				'card_id' => 2866,
				'unit_id' => 268
			],
			[
				'id' => 4830,
				'card_id' => 2960,
				'unit_id' => 268
			],
			[
				'id' => 4831,
				'card_id' => 2922,
				'unit_id' => 268
			],
			[
				'id' => 4832,
				'card_id' => 2924,
				'unit_id' => 268
			],
			[
				'id' => 4833,
				'card_id' => 2925,
				'unit_id' => 268
			],
			[
				'id' => 4834,
				'card_id' => 2928,
				'unit_id' => 268
			],
			[
				'id' => 4835,
				'card_id' => 2929,
				'unit_id' => 268
			],
			[
				'id' => 4836,
				'card_id' => 2931,
				'unit_id' => 268
			],
			[
				'id' => 4837,
				'card_id' => 2933,
				'unit_id' => 268
			],
			[
				'id' => 4838,
				'card_id' => 2936,
				'unit_id' => 268
			],
			[
				'id' => 4839,
				'card_id' => 2938,
				'unit_id' => 268
			],
			[
				'id' => 4840,
				'card_id' => 2940,
				'unit_id' => 268
			],
			[
				'id' => 4841,
				'card_id' => 178,
				'unit_id' => 269
			],
			[
				'id' => 4842,
				'card_id' => 2962,
				'unit_id' => 269
			],
			[
				'id' => 4843,
				'card_id' => 2963,
				'unit_id' => 269
			],
			[
				'id' => 4844,
				'card_id' => 2964,
				'unit_id' => 269
			],
			[
				'id' => 4845,
				'card_id' => 294,
				'unit_id' => 269
			],
			[
				'id' => 4846,
				'card_id' => 2966,
				'unit_id' => 269
			],
			[
				'id' => 4847,
				'card_id' => 2967,
				'unit_id' => 269
			],
			[
				'id' => 4848,
				'card_id' => 2968,
				'unit_id' => 269
			],
			[
				'id' => 4849,
				'card_id' => 2969,
				'unit_id' => 269
			],
			[
				'id' => 4850,
				'card_id' => 2970,
				'unit_id' => 269
			],
			[
				'id' => 4851,
				'card_id' => 2971,
				'unit_id' => 269
			],
			[
				'id' => 4852,
				'card_id' => 4436,
				'unit_id' => 269
			],
			[
				'id' => 4853,
				'card_id' => 2973,
				'unit_id' => 269
			],
			[
				'id' => 4854,
				'card_id' => 2531,
				'unit_id' => 269
			],
			[
				'id' => 4855,
				'card_id' => 2975,
				'unit_id' => 269
			],
			[
				'id' => 4856,
				'card_id' => 2976,
				'unit_id' => 269
			],
			[
				'id' => 4857,
				'card_id' => 2977,
				'unit_id' => 269
			],
			[
				'id' => 4858,
				'card_id' => 2991,
				'unit_id' => 269
			],
			[
				'id' => 4859,
				'card_id' => 2992,
				'unit_id' => 269
			],
			[
				'id' => 4860,
				'card_id' => 2986,
				'unit_id' => 269
			],
			[
				'id' => 4861,
				'card_id' => 2982,
				'unit_id' => 269
			],
			[
				'id' => 4862,
				'card_id' => 3004,
				'unit_id' => 269
			],
			[
				'id' => 4863,
				'card_id' => 2996,
				'unit_id' => 269
			],
			[
				'id' => 4864,
				'card_id' => 4435,
				'unit_id' => 269
			],
			[
				'id' => 4865,
				'card_id' => 217,
				'unit_id' => 270
			],
			[
				'id' => 4866,
				'card_id' => 3008,
				'unit_id' => 270
			],
			[
				'id' => 4867,
				'card_id' => 3009,
				'unit_id' => 270
			],
			[
				'id' => 4868,
				'card_id' => 219,
				'unit_id' => 270
			],
			[
				'id' => 4869,
				'card_id' => 3017,
				'unit_id' => 270
			],
			[
				'id' => 4870,
				'card_id' => 3014,
				'unit_id' => 270
			],
			[
				'id' => 4871,
				'card_id' => 3015,
				'unit_id' => 270
			],
			[
				'id' => 4872,
				'card_id' => 3012,
				'unit_id' => 270
			],
			[
				'id' => 4873,
				'card_id' => 3019,
				'unit_id' => 271
			],
			[
				'id' => 4874,
				'card_id' => 3020,
				'unit_id' => 271
			],
			[
				'id' => 4875,
				'card_id' => 3021,
				'unit_id' => 271
			],
			[
				'id' => 4876,
				'card_id' => 3022,
				'unit_id' => 271
			],
			[
				'id' => 4877,
				'card_id' => 3023,
				'unit_id' => 271
			],
			[
				'id' => 4878,
				'card_id' => 234,
				'unit_id' => 271
			],
			[
				'id' => 4879,
				'card_id' => 3025,
				'unit_id' => 271
			],
			[
				'id' => 4880,
				'card_id' => 790,
				'unit_id' => 271
			],
			[
				'id' => 4881,
				'card_id' => 920,
				'unit_id' => 271
			],
			[
				'id' => 4882,
				'card_id' => 3028,
				'unit_id' => 271
			],
			[
				'id' => 4883,
				'card_id' => 2342,
				'unit_id' => 271
			],
			[
				'id' => 4884,
				'card_id' => 3030,
				'unit_id' => 271
			],
			[
				'id' => 4885,
				'card_id' => 3031,
				'unit_id' => 271
			],
			[
				'id' => 4886,
				'card_id' => 3032,
				'unit_id' => 271
			],
			[
				'id' => 4887,
				'card_id' => 3033,
				'unit_id' => 271
			],
			[
				'id' => 4888,
				'card_id' => 3034,
				'unit_id' => 271
			],
			[
				'id' => 4889,
				'card_id' => 3035,
				'unit_id' => 271
			],
			[
				'id' => 4890,
				'card_id' => 3036,
				'unit_id' => 271
			],
			[
				'id' => 4891,
				'card_id' => 3037,
				'unit_id' => 271
			],
			[
				'id' => 4892,
				'card_id' => 3038,
				'unit_id' => 271
			],
			[
				'id' => 4893,
				'card_id' => 3039,
				'unit_id' => 272
			],
			[
				'id' => 4894,
				'card_id' => 3051,
				'unit_id' => 272
			],
			[
				'id' => 4895,
				'card_id' => 3059,
				'unit_id' => 272
			],
			[
				'id' => 4896,
				'card_id' => 3067,
				'unit_id' => 272
			],
			[
				'id' => 4897,
				'card_id' => 3057,
				'unit_id' => 272
			],
			[
				'id' => 4898,
				'card_id' => 3069,
				'unit_id' => 272
			],
			[
				'id' => 4899,
				'card_id' => 3060,
				'unit_id' => 272
			],
			[
				'id' => 4900,
				'card_id' => 3052,
				'unit_id' => 272
			],
			[
				'id' => 4901,
				'card_id' => 3040,
				'unit_id' => 272
			],
			[
				'id' => 4902,
				'card_id' => 3064,
				'unit_id' => 272
			],
			[
				'id' => 4903,
				'card_id' => 3050,
				'unit_id' => 272
			],
			[
				'id' => 4904,
				'card_id' => 3068,
				'unit_id' => 272
			],
			[
				'id' => 4905,
				'card_id' => 3072,
				'unit_id' => 273
			],
			[
				'id' => 4906,
				'card_id' => 3073,
				'unit_id' => 273
			],
			[
				'id' => 4907,
				'card_id' => 3079,
				'unit_id' => 273
			],
			[
				'id' => 4908,
				'card_id' => 3083,
				'unit_id' => 273
			],
			[
				'id' => 4909,
				'card_id' => 3088,
				'unit_id' => 273
			],
			[
				'id' => 4910,
				'card_id' => 3084,
				'unit_id' => 273
			],
			[
				'id' => 4911,
				'card_id' => 3074,
				'unit_id' => 273
			],
			[
				'id' => 4912,
				'card_id' => 3081,
				'unit_id' => 273
			],
			[
				'id' => 4913,
				'card_id' => 3093,
				'unit_id' => 273
			],
			[
				'id' => 4914,
				'card_id' => 3094,
				'unit_id' => 274
			],
			[
				'id' => 4915,
				'card_id' => 3095,
				'unit_id' => 274
			],
			[
				'id' => 4916,
				'card_id' => 3096,
				'unit_id' => 274
			],
			[
				'id' => 4917,
				'card_id' => 3097,
				'unit_id' => 274
			],
			[
				'id' => 4918,
				'card_id' => 3104,
				'unit_id' => 274
			],
			[
				'id' => 4919,
				'card_id' => 3113,
				'unit_id' => 274
			],
			[
				'id' => 4920,
				'card_id' => 3108,
				'unit_id' => 274
			],
			[
				'id' => 4921,
				'card_id' => 3105,
				'unit_id' => 274
			],
			[
				'id' => 4922,
				'card_id' => 3114,
				'unit_id' => 275
			],
			[
				'id' => 4923,
				'card_id' => 3115,
				'unit_id' => 275
			],
			[
				'id' => 4924,
				'card_id' => 3116,
				'unit_id' => 275
			],
			[
				'id' => 4925,
				'card_id' => 3117,
				'unit_id' => 275
			],
			[
				'id' => 4926,
				'card_id' => 3118,
				'unit_id' => 275
			],
			[
				'id' => 4927,
				'card_id' => 3119,
				'unit_id' => 275
			],
			[
				'id' => 4928,
				'card_id' => 3131,
				'unit_id' => 276
			],
			[
				'id' => 4929,
				'card_id' => 3132,
				'unit_id' => 276
			],
			[
				'id' => 4930,
				'card_id' => 3133,
				'unit_id' => 276
			],
			[
				'id' => 4931,
				'card_id' => 3134,
				'unit_id' => 276
			],
			[
				'id' => 4932,
				'card_id' => 3136,
				'unit_id' => 276
			],
			[
				'id' => 4933,
				'card_id' => 3138,
				'unit_id' => 276
			],
			[
				'id' => 4934,
				'card_id' => 3137,
				'unit_id' => 276
			],
			[
				'id' => 4935,
				'card_id' => 3135,
				'unit_id' => 276
			],
			[
				'id' => 4936,
				'card_id' => 3141,
				'unit_id' => 277
			],
			[
				'id' => 4937,
				'card_id' => 3148,
				'unit_id' => 277
			],
			[
				'id' => 4938,
				'card_id' => 3154,
				'unit_id' => 277
			],
			[
				'id' => 4939,
				'card_id' => 3161,
				'unit_id' => 277
			],
			[
				'id' => 4940,
				'card_id' => 3143,
				'unit_id' => 277
			],
			[
				'id' => 4941,
				'card_id' => 3150,
				'unit_id' => 277
			],
			[
				'id' => 4942,
				'card_id' => 3158,
				'unit_id' => 277
			],
			[
				'id' => 4943,
				'card_id' => 3162,
				'unit_id' => 277
			],
			[
				'id' => 4944,
				'card_id' => 3169,
				'unit_id' => 278
			],
			[
				'id' => 4945,
				'card_id' => 3171,
				'unit_id' => 278
			],
			[
				'id' => 4946,
				'card_id' => 3176,
				'unit_id' => 278
			],
			[
				'id' => 4947,
				'card_id' => 3174,
				'unit_id' => 278
			],
			[
				'id' => 4948,
				'card_id' => 3179,
				'unit_id' => 278
			],
			[
				'id' => 4949,
				'card_id' => 3178,
				'unit_id' => 278
			],
			[
				'id' => 4950,
				'card_id' => 3175,
				'unit_id' => 278
			],
			[
				'id' => 4951,
				'card_id' => 3181,
				'unit_id' => 279
			],
			[
				'id' => 4952,
				'card_id' => 3182,
				'unit_id' => 279
			],
			[
				'id' => 4953,
				'card_id' => 3183,
				'unit_id' => 279
			],
			[
				'id' => 4954,
				'card_id' => 3184,
				'unit_id' => 279
			],
			[
				'id' => 4955,
				'card_id' => 3185,
				'unit_id' => 279
			],
			[
				'id' => 4956,
				'card_id' => 3186,
				'unit_id' => 279
			],
			[
				'id' => 4957,
				'card_id' => 3187,
				'unit_id' => 279
			],
			[
				'id' => 4958,
				'card_id' => 3188,
				'unit_id' => 279
			],
			[
				'id' => 4959,
				'card_id' => 3189,
				'unit_id' => 279
			],
			[
				'id' => 4960,
				'card_id' => 3190,
				'unit_id' => 279
			],
			[
				'id' => 4961,
				'card_id' => 3191,
				'unit_id' => 279
			],
			[
				'id' => 4962,
				'card_id' => 3192,
				'unit_id' => 279
			],
			[
				'id' => 4963,
				'card_id' => 3193,
				'unit_id' => 279
			],
			[
				'id' => 4964,
				'card_id' => 3194,
				'unit_id' => 279
			],
			[
				'id' => 4965,
				'card_id' => 3195,
				'unit_id' => 280
			],
			[
				'id' => 4966,
				'card_id' => 3116,
				'unit_id' => 280
			],
			[
				'id' => 4967,
				'card_id' => 3222,
				'unit_id' => 280
			],
			[
				'id' => 4968,
				'card_id' => 3204,
				'unit_id' => 280
			],
			[
				'id' => 4969,
				'card_id' => 3120,
				'unit_id' => 280
			],
			[
				'id' => 4970,
				'card_id' => 3206,
				'unit_id' => 280
			],
			[
				'id' => 4971,
				'card_id' => 3211,
				'unit_id' => 280
			],
			[
				'id' => 4972,
				'card_id' => 3220,
				'unit_id' => 280
			],
			[
				'id' => 4973,
				'card_id' => 3118,
				'unit_id' => 280
			],
			[
				'id' => 4974,
				'card_id' => 3219,
				'unit_id' => 280
			],
			[
				'id' => 4975,
				'card_id' => 3199,
				'unit_id' => 280
			],
			[
				'id' => 4976,
				'card_id' => 3208,
				'unit_id' => 280
			],
			[
				'id' => 4977,
				'card_id' => 3217,
				'unit_id' => 280
			],
			[
				'id' => 4978,
				'card_id' => 3226,
				'unit_id' => 280
			],
			[
				'id' => 4979,
				'card_id' => 3200,
				'unit_id' => 280
			],
			[
				'id' => 4980,
				'card_id' => 3209,
				'unit_id' => 280
			],
			[
				'id' => 4981,
				'card_id' => 3218,
				'unit_id' => 280
			],
			[
				'id' => 4982,
				'card_id' => 3224,
				'unit_id' => 280
			],
			[
				'id' => 4983,
				'card_id' => 3207,
				'unit_id' => 280
			],
			[
				'id' => 4984,
				'card_id' => 3201,
				'unit_id' => 280
			],
			[
				'id' => 4985,
				'card_id' => 3227,
				'unit_id' => 281
			],
			[
				'id' => 4986,
				'card_id' => 3228,
				'unit_id' => 281
			],
			[
				'id' => 4987,
				'card_id' => 3229,
				'unit_id' => 281
			],
			[
				'id' => 4988,
				'card_id' => 3230,
				'unit_id' => 281
			],
			[
				'id' => 4989,
				'card_id' => 3231,
				'unit_id' => 281
			],
			[
				'id' => 4990,
				'card_id' => 3232,
				'unit_id' => 281
			],
			[
				'id' => 4991,
				'card_id' => 3233,
				'unit_id' => 281
			],
			[
				'id' => 4992,
				'card_id' => 3234,
				'unit_id' => 281
			],
			[
				'id' => 4993,
				'card_id' => 3235,
				'unit_id' => 281
			],
			[
				'id' => 4994,
				'card_id' => 3236,
				'unit_id' => 281
			],
			[
				'id' => 4995,
				'card_id' => 3237,
				'unit_id' => 281
			],
			[
				'id' => 4996,
				'card_id' => 3238,
				'unit_id' => 281
			],
			[
				'id' => 4997,
				'card_id' => 3239,
				'unit_id' => 281
			],
			[
				'id' => 4998,
				'card_id' => 3240,
				'unit_id' => 281
			],
			[
				'id' => 4999,
				'card_id' => 3241,
				'unit_id' => 281
			],
			[
				'id' => 5000,
				'card_id' => 3242,
				'unit_id' => 281
			],
			[
				'id' => 5001,
				'card_id' => 3243,
				'unit_id' => 281
			],
			[
				'id' => 5002,
				'card_id' => 3244,
				'unit_id' => 281
			],
			[
				'id' => 5003,
				'card_id' => 3245,
				'unit_id' => 281
			],
			[
				'id' => 5004,
				'card_id' => 3246,
				'unit_id' => 281
			],
			[
				'id' => 5005,
				'card_id' => 3247,
				'unit_id' => 282
			],
			[
				'id' => 5006,
				'card_id' => 3248,
				'unit_id' => 282
			],
			[
				'id' => 5007,
				'card_id' => 3249,
				'unit_id' => 282
			],
			[
				'id' => 5008,
				'card_id' => 3250,
				'unit_id' => 282
			],
			[
				'id' => 5009,
				'card_id' => 3251,
				'unit_id' => 282
			],
			[
				'id' => 5010,
				'card_id' => 3252,
				'unit_id' => 282
			],
			[
				'id' => 5011,
				'card_id' => 3253,
				'unit_id' => 282
			],
			[
				'id' => 5012,
				'card_id' => 3254,
				'unit_id' => 282
			],
			[
				'id' => 5013,
				'card_id' => 3258,
				'unit_id' => 282
			],
			[
				'id' => 5014,
				'card_id' => 3255,
				'unit_id' => 282
			],
			[
				'id' => 5015,
				'card_id' => 3262,
				'unit_id' => 282
			],
			[
				'id' => 5016,
				'card_id' => 3259,
				'unit_id' => 282
			],
			[
				'id' => 5017,
				'card_id' => 3263,
				'unit_id' => 283
			],
			[
				'id' => 5018,
				'card_id' => 3264,
				'unit_id' => 283
			],
			[
				'id' => 5019,
				'card_id' => 3265,
				'unit_id' => 283
			],
			[
				'id' => 5020,
				'card_id' => 3266,
				'unit_id' => 283
			],
			[
				'id' => 5021,
				'card_id' => 3269,
				'unit_id' => 283
			],
			[
				'id' => 5022,
				'card_id' => 3270,
				'unit_id' => 283
			],
			[
				'id' => 5023,
				'card_id' => 3280,
				'unit_id' => 283
			],
			[
				'id' => 5024,
				'card_id' => 3273,
				'unit_id' => 283
			],
			[
				'id' => 5025,
				'card_id' => 3282,
				'unit_id' => 283
			],
			[
				'id' => 5026,
				'card_id' => 3267,
				'unit_id' => 283
			],
			[
				'id' => 5027,
				'card_id' => 3303,
				'unit_id' => 283
			],
			[
				'id' => 5028,
				'card_id' => 3308,
				'unit_id' => 283
			],
			[
				'id' => 5029,
				'card_id' => 3293,
				'unit_id' => 283
			],
			[
				'id' => 5030,
				'card_id' => 3302,
				'unit_id' => 283
			],
			[
				'id' => 5031,
				'card_id' => 3311,
				'unit_id' => 284
			],
			[
				'id' => 5032,
				'card_id' => 3312,
				'unit_id' => 284
			],
			[
				'id' => 5033,
				'card_id' => 3323,
				'unit_id' => 284
			],
			[
				'id' => 5034,
				'card_id' => 3324,
				'unit_id' => 284
			],
			[
				'id' => 5035,
				'card_id' => 3329,
				'unit_id' => 284
			],
			[
				'id' => 5036,
				'card_id' => 3297,
				'unit_id' => 284
			],
			[
				'id' => 5037,
				'card_id' => 3334,
				'unit_id' => 284
			],
			[
				'id' => 5038,
				'card_id' => 3281,
				'unit_id' => 284
			],
			[
				'id' => 5039,
				'card_id' => 3290,
				'unit_id' => 284
			],
			[
				'id' => 5040,
				'card_id' => 3310,
				'unit_id' => 284
			],
			[
				'id' => 5041,
				'card_id' => 3335,
				'unit_id' => 284
			],
			[
				'id' => 5042,
				'card_id' => 3320,
				'unit_id' => 284
			],
			[
				'id' => 5043,
				'card_id' => 3288,
				'unit_id' => 284
			],
			[
				'id' => 5044,
				'card_id' => 3325,
				'unit_id' => 284
			],
			[
				'id' => 5045,
				'card_id' => 3367,
				'unit_id' => 285
			],
			[
				'id' => 5046,
				'card_id' => 3368,
				'unit_id' => 285
			],
			[
				'id' => 5047,
				'card_id' => 3371,
				'unit_id' => 285
			],
			[
				'id' => 5048,
				'card_id' => 3372,
				'unit_id' => 285
			],
			[
				'id' => 5049,
				'card_id' => 3383,
				'unit_id' => 285
			],
			[
				'id' => 5050,
				'card_id' => 3384,
				'unit_id' => 285
			],
			[
				'id' => 5051,
				'card_id' => 3369,
				'unit_id' => 285
			],
			[
				'id' => 5052,
				'card_id' => 3373,
				'unit_id' => 285
			],
			[
				'id' => 5053,
				'card_id' => 3376,
				'unit_id' => 285
			],
			[
				'id' => 5054,
				'card_id' => 3378,
				'unit_id' => 285
			],
			[
				'id' => 5055,
				'card_id' => 3379,
				'unit_id' => 285
			],
			[
				'id' => 5056,
				'card_id' => 3398,
				'unit_id' => 286
			],
			[
				'id' => 5057,
				'card_id' => 3323,
				'unit_id' => 286
			],
			[
				'id' => 5058,
				'card_id' => 3381,
				'unit_id' => 286
			],
			[
				'id' => 5059,
				'card_id' => 3336,
				'unit_id' => 286
			],
			[
				'id' => 5060,
				'card_id' => 3411,
				'unit_id' => 286
			],
			[
				'id' => 5061,
				'card_id' => 3367,
				'unit_id' => 286
			],
			[
				'id' => 5062,
				'card_id' => 3374,
				'unit_id' => 286
			],
			[
				'id' => 5063,
				'card_id' => 3424,
				'unit_id' => 286
			],
			[
				'id' => 5064,
				'card_id' => 3283,
				'unit_id' => 287
			],
			[
				'id' => 5065,
				'card_id' => 3429,
				'unit_id' => 287
			],
			[
				'id' => 5066,
				'card_id' => 3284,
				'unit_id' => 287
			],
			[
				'id' => 5067,
				'card_id' => 3430,
				'unit_id' => 287
			],
			[
				'id' => 5068,
				'card_id' => 3431,
				'unit_id' => 287
			],
			[
				'id' => 5069,
				'card_id' => 3433,
				'unit_id' => 287
			],
			[
				'id' => 5070,
				'card_id' => 3432,
				'unit_id' => 287
			],
			[
				'id' => 5071,
				'card_id' => 3434,
				'unit_id' => 287
			],
			[
				'id' => 5072,
				'card_id' => 3274,
				'unit_id' => 287
			],
			[
				'id' => 5073,
				'card_id' => 3441,
				'unit_id' => 287
			],
			[
				'id' => 5074,
				'card_id' => 3435,
				'unit_id' => 287
			],
			[
				'id' => 5075,
				'card_id' => 3438,
				'unit_id' => 287
			],
			[
				'id' => 5076,
				'card_id' => 3449,
				'unit_id' => 287
			],
			[
				'id' => 5077,
				'card_id' => 3452,
				'unit_id' => 287
			],
			[
				'id' => 5078,
				'card_id' => 3445,
				'unit_id' => 288
			],
			[
				'id' => 5079,
				'card_id' => 3371,
				'unit_id' => 288
			],
			[
				'id' => 5080,
				'card_id' => 3446,
				'unit_id' => 288
			],
			[
				'id' => 5081,
				'card_id' => 3372,
				'unit_id' => 288
			],
			[
				'id' => 5082,
				'card_id' => 3459,
				'unit_id' => 288
			],
			[
				'id' => 5083,
				'card_id' => 3461,
				'unit_id' => 288
			],
			[
				'id' => 5084,
				'card_id' => 3460,
				'unit_id' => 288
			],
			[
				'id' => 5085,
				'card_id' => 3462,
				'unit_id' => 288
			],
			[
				'id' => 5086,
				'card_id' => 3464,
				'unit_id' => 288
			],
			[
				'id' => 5087,
				'card_id' => 3469,
				'unit_id' => 288
			],
			[
				'id' => 5088,
				'card_id' => 3471,
				'unit_id' => 288
			],
			[
				'id' => 5089,
				'card_id' => 3478,
				'unit_id' => 288
			],
			[
				'id' => 5090,
				'card_id' => 3481,
				'unit_id' => 288
			],
			[
				'id' => 5091,
				'card_id' => 3486,
				'unit_id' => 288
			],
			[
				'id' => 5092,
				'card_id' => 3430,
				'unit_id' => 288
			],
			[
				'id' => 5093,
				'card_id' => 3491,
				'unit_id' => 288
			],
			[
				'id' => 5094,
				'card_id' => 3495,
				'unit_id' => 289
			],
			[
				'id' => 5095,
				'card_id' => 3496,
				'unit_id' => 289
			],
			[
				'id' => 5096,
				'card_id' => 3497,
				'unit_id' => 289
			],
			[
				'id' => 5097,
				'card_id' => 3498,
				'unit_id' => 289
			],
			[
				'id' => 5098,
				'card_id' => 3501,
				'unit_id' => 289
			],
			[
				'id' => 5099,
				'card_id' => 3502,
				'unit_id' => 289
			],
			[
				'id' => 5100,
				'card_id' => 3513,
				'unit_id' => 289
			],
			[
				'id' => 5101,
				'card_id' => 3514,
				'unit_id' => 289
			],
			[
				'id' => 5102,
				'card_id' => 3499,
				'unit_id' => 289
			],
			[
				'id' => 5103,
				'card_id' => 3504,
				'unit_id' => 289
			],
			[
				'id' => 5104,
				'card_id' => 3505,
				'unit_id' => 289
			],
			[
				'id' => 5105,
				'card_id' => 3507,
				'unit_id' => 289
			],
			[
				'id' => 5106,
				'card_id' => 3510,
				'unit_id' => 289
			],
			[
				'id' => 5107,
				'card_id' => 3512,
				'unit_id' => 289
			],
			[
				'id' => 5108,
				'card_id' => 3516,
				'unit_id' => 289
			],
			[
				'id' => 5109,
				'card_id' => 3517,
				'unit_id' => 289
			],
			[
				'id' => 5110,
				'card_id' => 3274,
				'unit_id' => 290
			],
			[
				'id' => 5111,
				'card_id' => 3324,
				'unit_id' => 290
			],
			[
				'id' => 5112,
				'card_id' => 3372,
				'unit_id' => 290
			],
			[
				'id' => 5113,
				'card_id' => 3446,
				'unit_id' => 290
			],
			[
				'id' => 5114,
				'card_id' => 3283,
				'unit_id' => 290
			],
			[
				'id' => 5115,
				'card_id' => 3391,
				'unit_id' => 290
			],
			[
				'id' => 5116,
				'card_id' => 3411,
				'unit_id' => 290
			],
			[
				'id' => 5117,
				'card_id' => 3429,
				'unit_id' => 290
			],
			[
				'id' => 5118,
				'card_id' => 3263,
				'unit_id' => 290
			],
			[
				'id' => 5119,
				'card_id' => 3312,
				'unit_id' => 290
			],
			[
				'id' => 5120,
				'card_id' => 3480,
				'unit_id' => 290
			],
			[
				'id' => 5121,
				'card_id' => 3481,
				'unit_id' => 290
			],
			[
				'id' => 5122,
				'card_id' => 3559,
				'unit_id' => 291
			],
			[
				'id' => 5123,
				'card_id' => 3572,
				'unit_id' => 291
			],
			[
				'id' => 5124,
				'card_id' => 3560,
				'unit_id' => 291
			],
			[
				'id' => 5125,
				'card_id' => 3573,
				'unit_id' => 291
			],
			[
				'id' => 5126,
				'card_id' => 3561,
				'unit_id' => 291
			],
			[
				'id' => 5127,
				'card_id' => 3574,
				'unit_id' => 291
			],
			[
				'id' => 5128,
				'card_id' => 3475,
				'unit_id' => 291
			],
			[
				'id' => 5129,
				'card_id' => 3476,
				'unit_id' => 291
			],
			[
				'id' => 5130,
				'card_id' => 3563,
				'unit_id' => 291
			],
			[
				'id' => 5131,
				'card_id' => 3576,
				'unit_id' => 291
			],
			[
				'id' => 5132,
				'card_id' => 3491,
				'unit_id' => 291
			],
			[
				'id' => 5133,
				'card_id' => 3492,
				'unit_id' => 291
			],
			[
				'id' => 5134,
				'card_id' => 3565,
				'unit_id' => 291
			],
			[
				'id' => 5135,
				'card_id' => 3578,
				'unit_id' => 291
			],
			[
				'id' => 5136,
				'card_id' => 3566,
				'unit_id' => 291
			],
			[
				'id' => 5137,
				'card_id' => 3579,
				'unit_id' => 291
			],
			[
				'id' => 5138,
				'card_id' => 3567,
				'unit_id' => 291
			],
			[
				'id' => 5139,
				'card_id' => 3580,
				'unit_id' => 291
			],
			[
				'id' => 5140,
				'card_id' => 3568,
				'unit_id' => 291
			],
			[
				'id' => 5141,
				'card_id' => 3581,
				'unit_id' => 291
			],
			[
				'id' => 5142,
				'card_id' => 3569,
				'unit_id' => 291
			],
			[
				'id' => 5143,
				'card_id' => 3582,
				'unit_id' => 291
			],
			[
				'id' => 5144,
				'card_id' => 3570,
				'unit_id' => 291
			],
			[
				'id' => 5145,
				'card_id' => 3583,
				'unit_id' => 291
			],
			[
				'id' => 5146,
				'card_id' => 3571,
				'unit_id' => 291
			],
			[
				'id' => 5147,
				'card_id' => 2626,
				'unit_id' => 292
			],
			[
				'id' => 5148,
				'card_id' => 3586,
				'unit_id' => 292
			],
			[
				'id' => 5149,
				'card_id' => 3587,
				'unit_id' => 292
			],
			[
				'id' => 5150,
				'card_id' => 3588,
				'unit_id' => 292
			],
			[
				'id' => 5151,
				'card_id' => 4439,
				'unit_id' => 292
			],
			[
				'id' => 5152,
				'card_id' => 478,
				'unit_id' => 292
			],
			[
				'id' => 5153,
				'card_id' => 3591,
				'unit_id' => 292
			],
			[
				'id' => 5154,
				'card_id' => 3592,
				'unit_id' => 292
			],
			[
				'id' => 5155,
				'card_id' => 3593,
				'unit_id' => 292
			],
			[
				'id' => 5156,
				'card_id' => 3594,
				'unit_id' => 292
			],
			[
				'id' => 5157,
				'card_id' => 3595,
				'unit_id' => 292
			],
			[
				'id' => 5158,
				'card_id' => 3596,
				'unit_id' => 292
			],
			[
				'id' => 5159,
				'card_id' => 3597,
				'unit_id' => 292
			],
			[
				'id' => 5160,
				'card_id' => 3598,
				'unit_id' => 292
			],
			[
				'id' => 5161,
				'card_id' => 3599,
				'unit_id' => 292
			],
			[
				'id' => 5162,
				'card_id' => 3600,
				'unit_id' => 292
			],
			[
				'id' => 5163,
				'card_id' => 3601,
				'unit_id' => 292
			],
			[
				'id' => 5164,
				'card_id' => 3602,
				'unit_id' => 292
			],
			[
				'id' => 5165,
				'card_id' => 3603,
				'unit_id' => 292
			],
			[
				'id' => 5166,
				'card_id' => 3604,
				'unit_id' => 292
			],
			[
				'id' => 5167,
				'card_id' => 3640,
				'unit_id' => 292
			],
			[
				'id' => 5168,
				'card_id' => 3605,
				'unit_id' => 292
			],
			[
				'id' => 5169,
				'card_id' => 3606,
				'unit_id' => 292
			],
			[
				'id' => 5170,
				'card_id' => 3613,
				'unit_id' => 292
			],
			[
				'id' => 5171,
				'card_id' => 3614,
				'unit_id' => 292
			],
			[
				'id' => 5172,
				'card_id' => 3628,
				'unit_id' => 292
			],
			[
				'id' => 5173,
				'card_id' => 3646,
				'unit_id' => 292
			],
			[
				'id' => 5174,
				'card_id' => 3611,
				'unit_id' => 292
			],
			[
				'id' => 5175,
				'card_id' => 3636,
				'unit_id' => 292
			],
			[
				'id' => 5176,
				'card_id' => 3620,
				'unit_id' => 292
			],
			[
				'id' => 5177,
				'card_id' => 3649,
				'unit_id' => 292
			],
			[
				'id' => 5178,
				'card_id' => 3624,
				'unit_id' => 292
			],
			[
				'id' => 5179,
				'card_id' => 3653,
				'unit_id' => 293
			],
			[
				'id' => 5180,
				'card_id' => 3654,
				'unit_id' => 293
			],
			[
				'id' => 5181,
				'card_id' => 3671,
				'unit_id' => 293
			],
			[
				'id' => 5182,
				'card_id' => 3672,
				'unit_id' => 293
			],
			[
				'id' => 5183,
				'card_id' => 3673,
				'unit_id' => 293
			],
			[
				'id' => 5184,
				'card_id' => 3674,
				'unit_id' => 293
			],
			[
				'id' => 5185,
				'card_id' => 3683,
				'unit_id' => 293
			],
			[
				'id' => 5186,
				'card_id' => 3684,
				'unit_id' => 293
			],
			[
				'id' => 5187,
				'card_id' => 3685,
				'unit_id' => 293
			],
			[
				'id' => 5188,
				'card_id' => 3686,
				'unit_id' => 293
			],
			[
				'id' => 5189,
				'card_id' => 3655,
				'unit_id' => 293
			],
			[
				'id' => 5190,
				'card_id' => 3660,
				'unit_id' => 293
			],
			[
				'id' => 5191,
				'card_id' => 3661,
				'unit_id' => 293
			],
			[
				'id' => 5192,
				'card_id' => 3664,
				'unit_id' => 293
			],
			[
				'id' => 5193,
				'card_id' => 4440,
				'unit_id' => 293
			],
			[
				'id' => 5194,
				'card_id' => 4441,
				'unit_id' => 293
			],
			[
				'id' => 5195,
				'card_id' => 4442,
				'unit_id' => 293
			],
			[
				'id' => 5196,
				'card_id' => 4444,
				'unit_id' => 293
			],
			[
				'id' => 5197,
				'card_id' => 3695,
				'unit_id' => 294
			],
			[
				'id' => 5198,
				'card_id' => 3696,
				'unit_id' => 294
			],
			[
				'id' => 5199,
				'card_id' => 3697,
				'unit_id' => 294
			],
			[
				'id' => 5200,
				'card_id' => 1807,
				'unit_id' => 294
			],
			[
				'id' => 5201,
				'card_id' => 3699,
				'unit_id' => 294
			],
			[
				'id' => 5202,
				'card_id' => 3700,
				'unit_id' => 294
			],
			[
				'id' => 5203,
				'card_id' => 1835,
				'unit_id' => 294
			],
			[
				'id' => 5204,
				'card_id' => 3702,
				'unit_id' => 294
			],
			[
				'id' => 5205,
				'card_id' => 3703,
				'unit_id' => 294
			],
			[
				'id' => 5206,
				'card_id' => 3704,
				'unit_id' => 294
			],
			[
				'id' => 5207,
				'card_id' => 3705,
				'unit_id' => 294
			],
			[
				'id' => 5208,
				'card_id' => 3706,
				'unit_id' => 294
			],
			[
				'id' => 5209,
				'card_id' => 2747,
				'unit_id' => 294
			],
			[
				'id' => 5210,
				'card_id' => 3708,
				'unit_id' => 294
			],
			[
				'id' => 5211,
				'card_id' => 3709,
				'unit_id' => 294
			],
			[
				'id' => 5212,
				'card_id' => 3710,
				'unit_id' => 294
			],
			[
				'id' => 5213,
				'card_id' => 3711,
				'unit_id' => 294
			],
			[
				'id' => 5214,
				'card_id' => 3712,
				'unit_id' => 294
			],
			[
				'id' => 5215,
				'card_id' => 3713,
				'unit_id' => 294
			],
			[
				'id' => 5216,
				'card_id' => 3714,
				'unit_id' => 294
			],
			[
				'id' => 5217,
				'card_id' => 3715,
				'unit_id' => 294
			],
			[
				'id' => 5218,
				'card_id' => 3716,
				'unit_id' => 294
			],
			[
				'id' => 5219,
				'card_id' => 3717,
				'unit_id' => 294
			],
			[
				'id' => 5220,
				'card_id' => 3718,
				'unit_id' => 294
			],
			[
				'id' => 5221,
				'card_id' => 3719,
				'unit_id' => 294
			],
			[
				'id' => 5222,
				'card_id' => 3720,
				'unit_id' => 294
			],
			[
				'id' => 5223,
				'card_id' => 3721,
				'unit_id' => 294
			],
			[
				'id' => 5224,
				'card_id' => 3722,
				'unit_id' => 294
			],
			[
				'id' => 5225,
				'card_id' => 3723,
				'unit_id' => 294
			],
			[
				'id' => 5226,
				'card_id' => 3724,
				'unit_id' => 294
			],
			[
				'id' => 5227,
				'card_id' => 3725,
				'unit_id' => 294
			],
			[
				'id' => 5228,
				'card_id' => 3726,
				'unit_id' => 294
			],
			[
				'id' => 5229,
				'card_id' => 3727,
				'unit_id' => 294
			],
			[
				'id' => 5230,
				'card_id' => 3728,
				'unit_id' => 294
			],
			[
				'id' => 5231,
				'card_id' => 3729,
				'unit_id' => 294
			],
			[
				'id' => 5232,
				'card_id' => 3730,
				'unit_id' => 294
			],
			[
				'id' => 5233,
				'card_id' => 3731,
				'unit_id' => 294
			],
			[
				'id' => 5234,
				'card_id' => 3732,
				'unit_id' => 294
			],
			[
				'id' => 5235,
				'card_id' => 3733,
				'unit_id' => 294
			],
			[
				'id' => 5236,
				'card_id' => 3734,
				'unit_id' => 294
			],
			[
				'id' => 5237,
				'card_id' => 3735,
				'unit_id' => 294
			],
			[
				'id' => 5238,
				'card_id' => 3736,
				'unit_id' => 294
			],
			[
				'id' => 5239,
				'card_id' => 1839,
				'unit_id' => 294
			],
			[
				'id' => 5240,
				'card_id' => 3738,
				'unit_id' => 294
			],
			[
				'id' => 5241,
				'card_id' => 3739,
				'unit_id' => 294
			],
			[
				'id' => 5242,
				'card_id' => 2362,
				'unit_id' => 294
			],
			[
				'id' => 5243,
				'card_id' => 3741,
				'unit_id' => 294
			],
			[
				'id' => 5244,
				'card_id' => 3742,
				'unit_id' => 294
			],
			[
				'id' => 5245,
				'card_id' => 3743,
				'unit_id' => 294
			],
			[
				'id' => 5246,
				'card_id' => 3744,
				'unit_id' => 294
			],
			[
				'id' => 5247,
				'card_id' => 3745,
				'unit_id' => 294
			],
			[
				'id' => 5248,
				'card_id' => 3746,
				'unit_id' => 294
			],
			[
				'id' => 5249,
				'card_id' => 3747,
				'unit_id' => 294
			],
			[
				'id' => 5250,
				'card_id' => 3748,
				'unit_id' => 294
			],
			[
				'id' => 5251,
				'card_id' => 3749,
				'unit_id' => 294
			],
			[
				'id' => 5252,
				'card_id' => 3750,
				'unit_id' => 294
			],
			[
				'id' => 5253,
				'card_id' => 3751,
				'unit_id' => 294
			],
			[
				'id' => 5254,
				'card_id' => 1339,
				'unit_id' => 294
			],
			[
				'id' => 5255,
				'card_id' => 3753,
				'unit_id' => 294
			],
			[
				'id' => 5256,
				'card_id' => 3754,
				'unit_id' => 294
			],
			[
				'id' => 5257,
				'card_id' => 3755,
				'unit_id' => 294
			],
			[
				'id' => 5258,
				'card_id' => 3756,
				'unit_id' => 294
			],
			[
				'id' => 5259,
				'card_id' => 3757,
				'unit_id' => 294
			],
			[
				'id' => 5260,
				'card_id' => 3758,
				'unit_id' => 294
			],
			[
				'id' => 5261,
				'card_id' => 3759,
				'unit_id' => 294
			],
			[
				'id' => 5262,
				'card_id' => 3760,
				'unit_id' => 294
			],
			[
				'id' => 5263,
				'card_id' => 3761,
				'unit_id' => 294
			],
			[
				'id' => 5264,
				'card_id' => 3762,
				'unit_id' => 294
			],
			[
				'id' => 5265,
				'card_id' => 3763,
				'unit_id' => 294
			],
			[
				'id' => 5266,
				'card_id' => 3764,
				'unit_id' => 295
			],
			[
				'id' => 5267,
				'card_id' => 3765,
				'unit_id' => 295
			],
			[
				'id' => 5268,
				'card_id' => 3766,
				'unit_id' => 295
			],
			[
				'id' => 5269,
				'card_id' => 3767,
				'unit_id' => 295
			],
			[
				'id' => 5270,
				'card_id' => 3768,
				'unit_id' => 295
			],
			[
				'id' => 5271,
				'card_id' => 3769,
				'unit_id' => 295
			],
			[
				'id' => 5272,
				'card_id' => 3770,
				'unit_id' => 295
			],
			[
				'id' => 5273,
				'card_id' => 3771,
				'unit_id' => 295
			],
			[
				'id' => 5274,
				'card_id' => 3772,
				'unit_id' => 295
			],
			[
				'id' => 5275,
				'card_id' => 2747,
				'unit_id' => 295
			],
			[
				'id' => 5276,
				'card_id' => 3774,
				'unit_id' => 295
			],
			[
				'id' => 5277,
				'card_id' => 3775,
				'unit_id' => 295
			],
			[
				'id' => 5278,
				'card_id' => 3776,
				'unit_id' => 295
			],
			[
				'id' => 5279,
				'card_id' => 3777,
				'unit_id' => 295
			],
			[
				'id' => 5280,
				'card_id' => 3778,
				'unit_id' => 295
			],
			[
				'id' => 5281,
				'card_id' => 3779,
				'unit_id' => 295
			],
			[
				'id' => 5282,
				'card_id' => 3780,
				'unit_id' => 295
			],
			[
				'id' => 5283,
				'card_id' => 3781,
				'unit_id' => 295
			],
			[
				'id' => 5284,
				'card_id' => 3782,
				'unit_id' => 295
			],
			[
				'id' => 5285,
				'card_id' => 3783,
				'unit_id' => 295
			],
			[
				'id' => 5286,
				'card_id' => 3784,
				'unit_id' => 295
			],
			[
				'id' => 5287,
				'card_id' => 3785,
				'unit_id' => 295
			],
			[
				'id' => 5288,
				'card_id' => 3786,
				'unit_id' => 295
			],
			[
				'id' => 5289,
				'card_id' => 3787,
				'unit_id' => 295
			],
			[
				'id' => 5290,
				'card_id' => 3722,
				'unit_id' => 295
			],
			[
				'id' => 5291,
				'card_id' => 3789,
				'unit_id' => 295
			],
			[
				'id' => 5292,
				'card_id' => 3790,
				'unit_id' => 295
			],
			[
				'id' => 5293,
				'card_id' => 3791,
				'unit_id' => 295
			],
			[
				'id' => 5294,
				'card_id' => 3792,
				'unit_id' => 295
			],
			[
				'id' => 5295,
				'card_id' => 3793,
				'unit_id' => 295
			],
			[
				'id' => 5296,
				'card_id' => 3794,
				'unit_id' => 295
			],
			[
				'id' => 5297,
				'card_id' => 3795,
				'unit_id' => 295
			],
			[
				'id' => 5298,
				'card_id' => 3796,
				'unit_id' => 295
			],
			[
				'id' => 5299,
				'card_id' => 3797,
				'unit_id' => 295
			],
			[
				'id' => 5300,
				'card_id' => 3798,
				'unit_id' => 295
			],
			[
				'id' => 5301,
				'card_id' => 3799,
				'unit_id' => 295
			],
			[
				'id' => 5302,
				'card_id' => 3746,
				'unit_id' => 295
			],
			[
				'id' => 5303,
				'card_id' => 3801,
				'unit_id' => 295
			],
			[
				'id' => 5304,
				'card_id' => 3802,
				'unit_id' => 295
			],
			[
				'id' => 5305,
				'card_id' => 1339,
				'unit_id' => 295
			],
			[
				'id' => 5306,
				'card_id' => 4445,
				'unit_id' => 295
			],
			[
				'id' => 5307,
				'card_id' => 4446,
				'unit_id' => 295
			],
			[
				'id' => 5308,
				'card_id' => 3755,
				'unit_id' => 295
			],
			[
				'id' => 5309,
				'card_id' => 3807,
				'unit_id' => 295
			],
			[
				'id' => 5310,
				'card_id' => 3808,
				'unit_id' => 295
			],
			[
				'id' => 5311,
				'card_id' => 3758,
				'unit_id' => 295
			],
			[
				'id' => 5312,
				'card_id' => 3810,
				'unit_id' => 295
			],
			[
				'id' => 5313,
				'card_id' => 3811,
				'unit_id' => 295
			],
			[
				'id' => 5314,
				'card_id' => 3812,
				'unit_id' => 296
			],
			[
				'id' => 5315,
				'card_id' => 3813,
				'unit_id' => 296
			],
			[
				'id' => 5316,
				'card_id' => 3814,
				'unit_id' => 296
			],
			[
				'id' => 5317,
				'card_id' => 3815,
				'unit_id' => 296
			],
			[
				'id' => 5318,
				'card_id' => 3816,
				'unit_id' => 296
			],
			[
				'id' => 5319,
				'card_id' => 3817,
				'unit_id' => 296
			],
			[
				'id' => 5320,
				'card_id' => 3818,
				'unit_id' => 296
			],
			[
				'id' => 5321,
				'card_id' => 3819,
				'unit_id' => 296
			],
			[
				'id' => 5322,
				'card_id' => 4448,
				'unit_id' => 296
			],
			[
				'id' => 5323,
				'card_id' => 4450,
				'unit_id' => 296
			],
			[
				'id' => 5324,
				'card_id' => 4447,
				'unit_id' => 296
			],
			[
				'id' => 5325,
				'card_id' => 4449,
				'unit_id' => 296
			],
			[
				'id' => 5326,
				'card_id' => 3820,
				'unit_id' => 296
			],
			[
				'id' => 5327,
				'card_id' => 3821,
				'unit_id' => 296
			],
			[
				'id' => 5328,
				'card_id' => 3822,
				'unit_id' => 296
			],
			[
				'id' => 5329,
				'card_id' => 3823,
				'unit_id' => 296
			],
			[
				'id' => 5330,
				'card_id' => 3824,
				'unit_id' => 296
			],
			[
				'id' => 5331,
				'card_id' => 3825,
				'unit_id' => 296
			],
			[
				'id' => 5332,
				'card_id' => 3826,
				'unit_id' => 296
			],
			[
				'id' => 5333,
				'card_id' => 3827,
				'unit_id' => 296
			],
			[
				'id' => 5334,
				'card_id' => 4454,
				'unit_id' => 296
			],
			[
				'id' => 5335,
				'card_id' => 4451,
				'unit_id' => 296
			],
			[
				'id' => 5336,
				'card_id' => 4453,
				'unit_id' => 296
			],
			[
				'id' => 5337,
				'card_id' => 4452,
				'unit_id' => 296
			],
			[
				'id' => 5338,
				'card_id' => 3828,
				'unit_id' => 296
			],
			[
				'id' => 5339,
				'card_id' => 3829,
				'unit_id' => 296
			],
			[
				'id' => 5340,
				'card_id' => 3830,
				'unit_id' => 296
			],
			[
				'id' => 5341,
				'card_id' => 3831,
				'unit_id' => 296
			],
			[
				'id' => 5342,
				'card_id' => 3832,
				'unit_id' => 296
			],
			[
				'id' => 5343,
				'card_id' => 3833,
				'unit_id' => 296
			],
			[
				'id' => 5344,
				'card_id' => 3834,
				'unit_id' => 296
			],
			[
				'id' => 5345,
				'card_id' => 3835,
				'unit_id' => 296
			],
			[
				'id' => 5346,
				'card_id' => 4455,
				'unit_id' => 296
			],
			[
				'id' => 5347,
				'card_id' => 4457,
				'unit_id' => 296
			],
			[
				'id' => 5348,
				'card_id' => 4456,
				'unit_id' => 296
			],
			[
				'id' => 5349,
				'card_id' => 4458,
				'unit_id' => 296
			],
			[
				'id' => 5350,
				'card_id' => 3836,
				'unit_id' => 296
			],
			[
				'id' => 5351,
				'card_id' => 3837,
				'unit_id' => 296
			],
			[
				'id' => 5352,
				'card_id' => 3838,
				'unit_id' => 296
			],
			[
				'id' => 5353,
				'card_id' => 3839,
				'unit_id' => 296
			],
			[
				'id' => 5354,
				'card_id' => 3840,
				'unit_id' => 296
			],
			[
				'id' => 5355,
				'card_id' => 3841,
				'unit_id' => 296
			],
			[
				'id' => 5356,
				'card_id' => 3842,
				'unit_id' => 296
			],
			[
				'id' => 5357,
				'card_id' => 3843,
				'unit_id' => 296
			],
			[
				'id' => 5358,
				'card_id' => 3844,
				'unit_id' => 296
			],
			[
				'id' => 5359,
				'card_id' => 3845,
				'unit_id' => 296
			],
			[
				'id' => 5360,
				'card_id' => 4461,
				'unit_id' => 296
			],
			[
				'id' => 5361,
				'card_id' => 4460,
				'unit_id' => 296
			],
			[
				'id' => 5362,
				'card_id' => 4463,
				'unit_id' => 296
			],
			[
				'id' => 5363,
				'card_id' => 4462,
				'unit_id' => 296
			],
			[
				'id' => 5364,
				'card_id' => 4459,
				'unit_id' => 296
			],
			[
				'id' => 5365,
				'card_id' => 3846,
				'unit_id' => 297
			],
			[
				'id' => 5366,
				'card_id' => 3847,
				'unit_id' => 297
			],
			[
				'id' => 5367,
				'card_id' => 3848,
				'unit_id' => 297
			],
			[
				'id' => 5368,
				'card_id' => 3849,
				'unit_id' => 297
			],
			[
				'id' => 5369,
				'card_id' => 3850,
				'unit_id' => 297
			],
			[
				'id' => 5370,
				'card_id' => 3851,
				'unit_id' => 297
			],
			[
				'id' => 5371,
				'card_id' => 3852,
				'unit_id' => 297
			],
			[
				'id' => 5372,
				'card_id' => 3853,
				'unit_id' => 297
			],
			[
				'id' => 5373,
				'card_id' => 3854,
				'unit_id' => 297
			],
			[
				'id' => 5374,
				'card_id' => 3289,
				'unit_id' => 298
			],
			[
				'id' => 5375,
				'card_id' => 3855,
				'unit_id' => 298
			],
			[
				'id' => 5376,
				'card_id' => 4464,
				'unit_id' => 298
			],
			[
				'id' => 5377,
				'card_id' => 3856,
				'unit_id' => 298
			],
			[
				'id' => 5378,
				'card_id' => 3253,
				'unit_id' => 298
			],
			[
				'id' => 5379,
				'card_id' => 3857,
				'unit_id' => 298
			],
			[
				'id' => 5380,
				'card_id' => 4465,
				'unit_id' => 298
			],
			[
				'id' => 5381,
				'card_id' => 3858,
				'unit_id' => 298
			],
			[
				'id' => 5382,
				'card_id' => 4466,
				'unit_id' => 298
			],
			[
				'id' => 5383,
				'card_id' => 3859,
				'unit_id' => 298
			],
			[
				'id' => 5384,
				'card_id' => 4467,
				'unit_id' => 298
			],
			[
				'id' => 5385,
				'card_id' => 3860,
				'unit_id' => 298
			],
			[
				'id' => 5386,
				'card_id' => 4468,
				'unit_id' => 298
			],
			[
				'id' => 5387,
				'card_id' => 3861,
				'unit_id' => 298
			],
			[
				'id' => 5388,
				'card_id' => 4469,
				'unit_id' => 298
			],
			[
				'id' => 5389,
				'card_id' => 3862,
				'unit_id' => 298
			],
			[
				'id' => 5390,
				'card_id' => 4470,
				'unit_id' => 298
			],
			[
				'id' => 5391,
				'card_id' => 3863,
				'unit_id' => 298
			],
			[
				'id' => 5392,
				'card_id' => 4471,
				'unit_id' => 298
			],
			[
				'id' => 5393,
				'card_id' => 3864,
				'unit_id' => 298
			],
			[
				'id' => 5394,
				'card_id' => 3259,
				'unit_id' => 298
			],
			[
				'id' => 5395,
				'card_id' => 3865,
				'unit_id' => 298
			],
			[
				'id' => 5396,
				'card_id' => 4472,
				'unit_id' => 298
			],
			[
				'id' => 5397,
				'card_id' => 3866,
				'unit_id' => 298
			],
			[
				'id' => 5398,
				'card_id' => 4473,
				'unit_id' => 298
			],
			[
				'id' => 5399,
				'card_id' => 3867,
				'unit_id' => 298
			],
			[
				'id' => 5400,
				'card_id' => 4474,
				'unit_id' => 298
			],
			[
				'id' => 5401,
				'card_id' => 3868,
				'unit_id' => 298
			],
			[
				'id' => 5402,
				'card_id' => 4475,
				'unit_id' => 298
			],
			[
				'id' => 5403,
				'card_id' => 3869,
				'unit_id' => 298
			],
			[
				'id' => 5404,
				'card_id' => 4476,
				'unit_id' => 298
			],
			[
				'id' => 5405,
				'card_id' => 3870,
				'unit_id' => 298
			],
			[
				'id' => 5406,
				'card_id' => 3329,
				'unit_id' => 298
			],
			[
				'id' => 5407,
				'card_id' => 3871,
				'unit_id' => 298
			],
			[
				'id' => 5408,
				'card_id' => 4477,
				'unit_id' => 298
			],
			[
				'id' => 5409,
				'card_id' => 3872,
				'unit_id' => 298
			],
			[
				'id' => 5410,
				'card_id' => 4478,
				'unit_id' => 298
			],
			[
				'id' => 5411,
				'card_id' => 3873,
				'unit_id' => 298
			],
			[
				'id' => 5412,
				'card_id' => 3423,
				'unit_id' => 298
			],
			[
				'id' => 5413,
				'card_id' => 3874,
				'unit_id' => 298
			],
			[
				'id' => 5414,
				'card_id' => 3182,
				'unit_id' => 300
			],
			[
				'id' => 5415,
				'card_id' => 3188,
				'unit_id' => 300
			],
			[
				'id' => 5416,
				'card_id' => 3186,
				'unit_id' => 300
			],
			[
				'id' => 5417,
				'card_id' => 3184,
				'unit_id' => 300
			],
			[
				'id' => 5418,
				'card_id' => 3183,
				'unit_id' => 300
			],
			[
				'id' => 5419,
				'card_id' => 3185,
				'unit_id' => 300
			],
			[
				'id' => 5420,
				'card_id' => 3187,
				'unit_id' => 300
			],
			[
				'id' => 5421,
				'card_id' => 3181,
				'unit_id' => 300
			],
			[
				'id' => 5422,
				'card_id' => 3914,
				'unit_id' => 300
			],
			[
				'id' => 5423,
				'card_id' => 3915,
				'unit_id' => 300
			],
			[
				'id' => 5424,
				'card_id' => 3924,
				'unit_id' => 300
			],
			[
				'id' => 5425,
				'card_id' => 3925,
				'unit_id' => 300
			],
			[
				'id' => 5426,
				'card_id' => 3919,
				'unit_id' => 300
			],
			[
				'id' => 5427,
				'card_id' => 3918,
				'unit_id' => 300
			],
			[
				'id' => 5428,
				'card_id' => 3929,
				'unit_id' => 300
			],
			[
				'id' => 5429,
				'card_id' => 3928,
				'unit_id' => 300
			],
			[
				'id' => 5430,
				'card_id' => 3930,
				'unit_id' => 301
			],
			[
				'id' => 5431,
				'card_id' => 3931,
				'unit_id' => 301
			],
			[
				'id' => 5432,
				'card_id' => 3932,
				'unit_id' => 301
			],
			[
				'id' => 5433,
				'card_id' => 3933,
				'unit_id' => 301
			],
			[
				'id' => 5434,
				'card_id' => 3934,
				'unit_id' => 301
			],
			[
				'id' => 5435,
				'card_id' => 3935,
				'unit_id' => 301
			],
			[
				'id' => 5436,
				'card_id' => 3937,
				'unit_id' => 301
			],
			[
				'id' => 5437,
				'card_id' => 3938,
				'unit_id' => 301
			],
			[
				'id' => 5438,
				'card_id' => 3941,
				'unit_id' => 301
			],
			[
				'id' => 5439,
				'card_id' => 3943,
				'unit_id' => 301
			],
			[
				'id' => 5440,
				'card_id' => 3944,
				'unit_id' => 301
			],
			[
				'id' => 5441,
				'card_id' => 3947,
				'unit_id' => 301
			],
			[
				'id' => 5442,
				'card_id' => 3948,
				'unit_id' => 302
			],
			[
				'id' => 5443,
				'card_id' => 3949,
				'unit_id' => 302
			],
			[
				'id' => 5444,
				'card_id' => 3950,
				'unit_id' => 302
			],
			[
				'id' => 5445,
				'card_id' => 3951,
				'unit_id' => 302
			],
			[
				'id' => 5446,
				'card_id' => 3952,
				'unit_id' => 302
			],
			[
				'id' => 5447,
				'card_id' => 3953,
				'unit_id' => 302
			],
			[
				'id' => 5448,
				'card_id' => 3955,
				'unit_id' => 302
			],
			[
				'id' => 5449,
				'card_id' => 3957,
				'unit_id' => 302
			],
			[
				'id' => 5450,
				'card_id' => 3962,
				'unit_id' => 302
			],
			[
				'id' => 5451,
				'card_id' => 3964,
				'unit_id' => 302
			],
			[
				'id' => 5452,
				'card_id' => 3969,
				'unit_id' => 302
			],
			[
				'id' => 5453,
				'card_id' => 3970,
				'unit_id' => 303
			],
			[
				'id' => 5454,
				'card_id' => 3971,
				'unit_id' => 303
			],
			[
				'id' => 5455,
				'card_id' => 3972,
				'unit_id' => 303
			],
			[
				'id' => 5456,
				'card_id' => 3973,
				'unit_id' => 303
			],
			[
				'id' => 5457,
				'card_id' => 3974,
				'unit_id' => 303
			],
			[
				'id' => 5458,
				'card_id' => 3975,
				'unit_id' => 303
			],
			[
				'id' => 5459,
				'card_id' => 3976,
				'unit_id' => 303
			],
			[
				'id' => 5460,
				'card_id' => 3979,
				'unit_id' => 303
			],
			[
				'id' => 5461,
				'card_id' => 3981,
				'unit_id' => 303
			],
			[
				'id' => 5462,
				'card_id' => 3983,
				'unit_id' => 303
			],
			[
				'id' => 5463,
				'card_id' => 3984,
				'unit_id' => 303
			],
			[
				'id' => 5464,
				'card_id' => 3970,
				'unit_id' => 304
			],
			[
				'id' => 5465,
				'card_id' => 3995,
				'unit_id' => 304
			],
			[
				'id' => 5466,
				'card_id' => 3978,
				'unit_id' => 304
			],
			[
				'id' => 5467,
				'card_id' => 4011,
				'unit_id' => 304
			],
			[
				'id' => 5468,
				'card_id' => 3972,
				'unit_id' => 304
			],
			[
				'id' => 5469,
				'card_id' => 3997,
				'unit_id' => 304
			],
			[
				'id' => 5470,
				'card_id' => 3980,
				'unit_id' => 304
			],
			[
				'id' => 5471,
				'card_id' => 4013,
				'unit_id' => 304
			],
			[
				'id' => 5472,
				'card_id' => 4479,
				'unit_id' => 304
			],
			[
				'id' => 5473,
				'card_id' => 4480,
				'unit_id' => 304
			],
			[
				'id' => 5474,
				'card_id' => 4482,
				'unit_id' => 304
			],
			[
				'id' => 5475,
				'card_id' => 4481,
				'unit_id' => 304
			],
			[
				'id' => 5476,
				'card_id' => 4483,
				'unit_id' => 304
			],
			[
				'id' => 5477,
				'card_id' => 4484,
				'unit_id' => 304
			],
			[
				'id' => 5478,
				'card_id' => 4026,
				'unit_id' => 305
			],
			[
				'id' => 5479,
				'card_id' => 4027,
				'unit_id' => 305
			],
			[
				'id' => 5480,
				'card_id' => 1747,
				'unit_id' => 305
			],
			[
				'id' => 5481,
				'card_id' => 4485,
				'unit_id' => 305
			],
			[
				'id' => 5482,
				'card_id' => 4028,
				'unit_id' => 305
			],
			[
				'id' => 5483,
				'card_id' => 4029,
				'unit_id' => 305
			],
			[
				'id' => 5484,
				'card_id' => 550,
				'unit_id' => 305
			],
			[
				'id' => 5485,
				'card_id' => 4486,
				'unit_id' => 305
			],
			[
				'id' => 5486,
				'card_id' => 4030,
				'unit_id' => 305
			],
			[
				'id' => 5487,
				'card_id' => 4031,
				'unit_id' => 305
			],
			[
				'id' => 5488,
				'card_id' => 288,
				'unit_id' => 305
			],
			[
				'id' => 5489,
				'card_id' => 4487,
				'unit_id' => 305
			],
			[
				'id' => 5490,
				'card_id' => 4032,
				'unit_id' => 305
			],
			[
				'id' => 5491,
				'card_id' => 4033,
				'unit_id' => 305
			],
			[
				'id' => 5492,
				'card_id' => 1746,
				'unit_id' => 305
			],
			[
				'id' => 5493,
				'card_id' => 4488,
				'unit_id' => 305
			],
			[
				'id' => 5494,
				'card_id' => 4034,
				'unit_id' => 305
			],
			[
				'id' => 5495,
				'card_id' => 4035,
				'unit_id' => 305
			],
			[
				'id' => 5496,
				'card_id' => 4495,
				'unit_id' => 305
			],
			[
				'id' => 5497,
				'card_id' => 4489,
				'unit_id' => 305
			],
			[
				'id' => 5498,
				'card_id' => 4036,
				'unit_id' => 305
			],
			[
				'id' => 5499,
				'card_id' => 4037,
				'unit_id' => 305
			],
			[
				'id' => 5500,
				'card_id' => 2608,
				'unit_id' => 305
			],
			[
				'id' => 5501,
				'card_id' => 4490,
				'unit_id' => 305
			],
			[
				'id' => 5502,
				'card_id' => 4038,
				'unit_id' => 305
			],
			[
				'id' => 5503,
				'card_id' => 4039,
				'unit_id' => 305
			],
			[
				'id' => 5504,
				'card_id' => 2610,
				'unit_id' => 305
			],
			[
				'id' => 5505,
				'card_id' => 4491,
				'unit_id' => 305
			],
			[
				'id' => 5506,
				'card_id' => 4040,
				'unit_id' => 305
			],
			[
				'id' => 5507,
				'card_id' => 4041,
				'unit_id' => 305
			],
			[
				'id' => 5508,
				'card_id' => 2598,
				'unit_id' => 305
			],
			[
				'id' => 5509,
				'card_id' => 4492,
				'unit_id' => 305
			],
			[
				'id' => 5510,
				'card_id' => 4042,
				'unit_id' => 305
			],
			[
				'id' => 5511,
				'card_id' => 4043,
				'unit_id' => 305
			],
			[
				'id' => 5512,
				'card_id' => 1659,
				'unit_id' => 305
			],
			[
				'id' => 5513,
				'card_id' => 4493,
				'unit_id' => 305
			],
			[
				'id' => 5514,
				'card_id' => 4044,
				'unit_id' => 305
			],
			[
				'id' => 5515,
				'card_id' => 4045,
				'unit_id' => 305
			],
			[
				'id' => 5516,
				'card_id' => 4496,
				'unit_id' => 305
			],
			[
				'id' => 5517,
				'card_id' => 4494,
				'unit_id' => 305
			],
			[
				'id' => 5518,
				'card_id' => 1833,
				'unit_id' => 306
			],
			[
				'id' => 5519,
				'card_id' => 4047,
				'unit_id' => 306
			],
			[
				'id' => 5520,
				'card_id' => 1807,
				'unit_id' => 306
			],
			[
				'id' => 5521,
				'card_id' => 4049,
				'unit_id' => 306
			],
			[
				'id' => 5522,
				'card_id' => 4050,
				'unit_id' => 306
			],
			[
				'id' => 5523,
				'card_id' => 4051,
				'unit_id' => 306
			],
			[
				'id' => 5524,
				'card_id' => 4052,
				'unit_id' => 306
			],
			[
				'id' => 5525,
				'card_id' => 4053,
				'unit_id' => 306
			],
			[
				'id' => 5526,
				'card_id' => 4054,
				'unit_id' => 306
			],
			[
				'id' => 5527,
				'card_id' => 4055,
				'unit_id' => 306
			],
			[
				'id' => 5528,
				'card_id' => 4056,
				'unit_id' => 306
			],
			[
				'id' => 5529,
				'card_id' => 4057,
				'unit_id' => 306
			],
		];
		parent::init();
	}
}
