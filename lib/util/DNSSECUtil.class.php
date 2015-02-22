<?php
namespace dns\util;

/**
 * @author      Jan Altensen (Stricted)
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @copyright   2015 Jan Altensen (Stricted)
 */
class DNSSECUtil {
	
	public static function calculateDS ($owner, $algorithm, $publicKey) {
		$owner = self::convertOwner($owner);
		$flags = '0101';
		$protocol = '03';
		$algorithm = '0'.dechex($algorithm);
		$publicKey = bin2hex(base64_decode($publicKey));
		
		$string = hex2bin($owner.$flags.$protocol.$algorithm.$publicKey);
		
		$sha1 = sha1($string);
		$sha256 = hash('sha256', $string);
		
		return array('sha1' => $sha1, 'sha256' => $sha256);
	}
	
	public static convertOwner ($owner) {
		$return = '';
		
		$data = explode(".", $owner);
		$return .= '0'.dechex(strlen($data[0]));
		$data[0] = str_split($data[0]);
		for ($i = 0; $i < count($data[0]); $i++) {
			$byte = strtoupper(dechex(ord($data[0][$i])));
			$byte = str_repeat('0', 2 - strlen($byte)).$byte;
			$return .= $byte;
		}
		
		$return .= '0'.dechex(strlen($data[1]));
		$data[1] = str_split($data[1]);
		
		for ($i = 0; $i < count($data[1]); $i++) {
			$byte = strtoupper(dechex(ord($data[1][$i])));
			$byte = str_repeat('0', 2 - strlen($byte)).$byte;
			$return .= $byte;
		}
		
		$return .= '00';
		
		return $return;
	}
	
	public static function validatePublicKey ($content) {
		$pattern = "; This is a (key|zone)-signing key, keyid (?P<keyid>[0-9]+), for (?P<domain>[\s\S]+)\.\n";
		$pattern .= "; Created: (?P<created>[0-9]+) \(([a-z0-9: ]+)\)\n";
		$pattern .= "; Publish: (?P<publish>[0-9]+) \(([a-z0-9: ]+)\)\n";
		$pattern .= "; Activate: (?P<activate>[0-9]+) \(([a-z0-9: ]+)\)\n";
		$pattern .= "([\s\S]+). IN DNSKEY (?P<type>[0-9]+) ([0-9]+) (?P<algorithm>[0-9]+) (?P<key>[\s\S]+)";
		preg_match('/'.$pattern.'/i', $content, $matches);
		if (!empty($matches)) {
			$data = explode(' ', $matches['key']);
			foreach ($data as $d) {
				if (base64_encode(base64_decode($d, true)) !== $d) {
					return false;
				}
			}
		}
		else {
			return false;
		}
		
		return true;
	}
	
	public static function validatePrivateKey ($content) {
		$pattern = "Private-key-format: v([0-9a-z.]+)\n";
		$pattern .= "Algorithm: (?P<algorithm>[0-9]+) \(([0-9a-z\-]+)\)\n";
		$pattern .= "Modulus: (?P<modulus>[\s\S]+)\n";
		$pattern .= "PublicExponent: (?P<publicexponent>[\s\S]+)\n";
		$pattern .= "Prime1: (?P<prime1>[\s\S]+)\n";
		$pattern .= "Prime2: (?P<prime2>[\s\S]+)\n";
		$pattern .= "Exponent1: (?P<exponent1>[\s\S]+)\n";
		$pattern .= "Exponent2: (?P<exponent2>[\s\S]+)\n";
		$pattern .= "Coefficient: (?P<coefficient>[\s\S]+)\n";
		$pattern .= "Created: (?P<created>[0-9]+)\n";
		$pattern .= "Publish: (?P<publish>[0-9]+)\n";
		$pattern .= "Activate: (?P<activate>[0-9]+)";

		preg_match('/'.$pattern.'/i', $content, $matches);
		if (!empty($matches)) {
			/* to be continued */
		}
		else {
			return false;
		}
		
		return true;
	}
}