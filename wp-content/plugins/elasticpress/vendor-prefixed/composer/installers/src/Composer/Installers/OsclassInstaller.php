<?php
/**
 * @license MIT
 *
 * Modified by Taylor Lovett on 11-June-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace ElasticPress\Vendor_Prefixed\Composer\Installers;

class OsclassInstaller extends BaseInstaller
{
    
    /** @var array<string, string> */
    protected $locations = array(
        'plugin' => 'oc-content/plugins/{$name}/',
        'theme' => 'oc-content/themes/{$name}/',
        'language' => 'oc-content/languages/{$name}/',
    );
}
