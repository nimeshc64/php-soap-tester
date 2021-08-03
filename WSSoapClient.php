<?php

class WSSoapClient extends \SoapClient
{
    /**
     * WS-Security Username
     * @var string
     */
    private $username;

    /**
     * WS-Security Password
     * @var string
     */
    private $password;

    /**
     * Set WS-Security credentials
     *
     * @param string $username
     * @param string $password
     */
    public function __setUsernameToken($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Overwrites the original method adding the security header. As you can
     * see, if you want to add more headers, the method needs to be modified.
     */
    public function __soapCall($function_name, $arguments, $options=null, $input_headers=null, &$output_headers=null)
    {
        return parent::__soapCall($function_name, $arguments, $options, $this->generateWSSecurityHeader());
    }

    /**
     * Generate password digest
     *
     * Using the password directly may work also, but it's not secure to
     * transmit it without encryption. And anyway, at least with
     * axis+wss4j, the nonce and timestamp are mandatory anyway.
     *
     * @return string   base64 encoded password digest
     */
    private function generatePasswordDigest()
    {
        // Can use rand() to repeat the word if the server is under high load
        $this->nonce = mt_rand();
        $this->timestamp = gmdate('Y-m-d\TH:i:s\Z');

        $packedNonce = pack('H*', $this->nonce);
        $packedTimestamp = pack('a*', $this->timestamp);
        $packedPassword = pack('a*', $this->password);

        $hash = sha1($packedNonce . $packedTimestamp . $packedPassword);
        $packedHash = pack('H*', $hash);

        return base64_encode($packedHash);
    }

    /**
     * Generates WS-Security headers
     *
     * @return \SoapHeader
     */
    private function generateWSSecurityHeader()
    {
        $passwordDigest = $this->generatePasswordDigest();

        $xml = '
<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
    <wsse:UsernameToken>
        <wsse:Username>' . $this->username . '</wsse:Username>
        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">' . $passwordDigest . '</wsse:Password>
        <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . base64_encode(pack('H*', $this->nonce)) . '</wsse:Nonce>
        <wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' . $this->timestamp . '</wsu:Created>
    </wsse:UsernameToken>
</wsse:Security>
';

        return new \SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new \SoapVar($xml, XSD_ANYXML),
            true
        );
    }
}