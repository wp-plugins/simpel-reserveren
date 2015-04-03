<?php
class DBK_SR_i18n
{

    private $domain;

    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            $this->domain,
            false,
            dirname(dirname(plugin_basename(__FILE__))).'/languages/'
        );
    }
    /**
     * Set the domain equal to that of the specified domain.
     *
     * @since 1.0.0
     * @param string $domain The domain that represents the locale of this plugin.
     */
    public function set_domain($domain)
    {
        $this->domain = $domain;
    }
}
