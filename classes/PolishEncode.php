
<?php
/** PolishEncode
 * @brief	Conversion ISO-8859-2 and WINDOWS-1250 to UTF-8.
 * 			Based on BIBLIOTEKA PL (http://gajdaw.pl/download/varia/polskie-ogonki-na-www/examples/7-1-biblioteka-pl.zip).
 * @todo
 * - support recognize utf-* mixed string
 * - support convert utf-* mixed string to utf
 * - enhance docs
 */

// BIBLIOTEKA PL
// ver 0.9
// 2005.09.20
//
//
//Kodowanie polskich znaków:
//
//  ISO-8859-2      - polskie znaki iso
//  WINDOWS-1250    - polskie znaki win
//  ASCII           - brak jakichkolwiek polskich znaków
//  WIN-AND-ISO     - plik zepsuty: zawiera zarówno znaki WIN jak i ISO (specyficzne)
//  WIN-OR-ISO      - plik nie zawiera znaków specyficznych żadnego kodu, ale zawiera znaki wspólne
//  UTF-8           - kodowanie utf-8
//  UTF-16          - kodowanie utf-16


// Biblioteka mb, a w szczególności funkcja mb_detect_encoding() nie
// umożliwiajš wykrycia innego kodowania niż ISO-8859-2 lub UTF-8 (w stosunku do polskich znaków).
// Wywołanie:
//     echo mb_detect_encoding($org, 'ISO-8859-2, WINDOWS-1250, UTF-8');
// nie da pożšdanych efektów, gdyż kodowanie WINDOWS-1250 nie jest rozpoznawane.
//
// Ponadto funkcja iconv() w przypadku napotkania niedozwolonych znaków kończy przetwarzanie.
// Stąd potrzeba przygotowania własnej funkcji pl_detect().
//
//
// (c)2005 gajdaw
//  http://www.gajdaw.pl
//
//

class PolishEncode{
	/** Supported encodings:
	 * - UTF-8
	 * - ISO-8859-2
	 * - WINDOWS-1250
	 */
	const ENCODE_UTF = 'UTF-8';
	const ENCODE_ISO = 'ISO-8859-2';
	const ENCODE_WIN = 'WINDOWS-1250';

	/** Detect encode result:
	 * - UTF-8
	 * - ISO-8859-2
	 * - WINDOWS-1250
	 * - ISO-8859-2 or WINDOWS-1250
	 * - ISO-8859-2 and WINDOWS-1250 mixed
	 * - unrecognized
	 */
	const DETECTED_UTF		= 0;
	const DETECTED_ISO		= 1;
	const DETECTED_WIN		= 2;
	const DETECTED_ISO_WIN_MIX	= 3;
	const DETECTED_ISO_WIN_OR	= 4;
	const DETECTED_UNRECOGNIZED	= 5;

        const DETECTED_TO_ENCODE = [0 => 'UTF-8', 1 => 'ISO-8859-2', 2 => 'WINDOWS-1250', 3 => false, 4 => false, 5 => false];
	const COUNT_CHARS = 3;

	/** Char code sets:
	 * - common codes for: "ćęłńóżĆĘŁŃÓŻ"
	 * - ISO-8859-2 specific codes for "ąśźĄŚŹ"
	 * - WINDOWS-1250 specific codes
	 */
	private static $_CHARS_ISO	= ["\xb1", "\xb6", "\xbc", "\xa1", "\xa6", "\xac"];
	private static $_CHARS_WIN	= ["\xb9", "\x9c", "\x9f", "\xa5", "\x8c", "\x8f"];
	private static $_CHARS_ISO_WIN	= ["\xe6", "\xea", "\xb3", "\xf1", "\xf3", "\xbf", "\xc6", "\xca", "\xa3", "\xd1", "\xd3", "\xaf"];

	private $_Content	= null;
	private $_Detected	= null;
	private $_Encode	= null;
	private $_Chars		= [];

	public function setContent(string $content){
            if($content === null)
                throw new InvalidArgumentException('Content can not be null');

            $this->_Content = $content;
            $this->_Chars = str_split(count_chars($this->_Content, self::COUNT_CHARS));
            $this->_Encode = null;
            $this->_Detected = $this->_getDetected();
            $this->_Encode = self::DETECTED_TO_ENCODE[$this->_Detected];
	}
        
	private function _getDetected(){
		$iso = $this->isISO();
		$win = $this->isWIN();

		if ($iso && $win)
                    return self::DETECTED_ISO_WIN_MIX;
		elseif ($iso && !$win)
                    return self::DETECTED_ISO;
		elseif (!$iso && $win)
                    return self::DETECTED_WIN;
		elseif((bool)array_intersect($this->_Chars, self::$_CHARS_ISO_WIN))
                    return self::DETECTED_ISO_WIN_OR;
		elseif($this->isUTF())
                    return self::DETECTED_UTF;
		else
                    return self::DETECTED_UNRECOGNIZED;
	}

	public function getDetected(){
		return $this->_Detected;
	}

	public function getEncode(){
		return $this->_Encode;
	}

	public function isUTF(){
            if($this->_Encode === NULL)
                return mb_detect_encoding($this->_Content, self::ENCODE_UTF) === self::ENCODE_UTF;

            return $this->_Encode === self::DETECTED_UTF;
	}

	public function isISO(){
            if($this->_Encode === null)
                return (bool)array_intersect($this->_Chars, self::$_CHARS_ISO);

            return $this->_Encode === self::DETECTED_UTF;
        }

	public function isWIN(){
            if ($this->_Encode === null)
                return (bool)array_intersect($this->_Chars, self::$_CHARS_WIN);

            return $this->_Encode === self::DETECTED_UTF;
	}
        
        public function convertToUTF(&$content, $ref = true){
            $this->setContent($content);
            
            if($ref)
                $content = $this->getUTF();
            else
                return $this->getUTF();
        }

	public function getUTF(){
            if ($this->_Encode === self::ENCODE_UTF)
                return $this->_Content;
            elseif ($this->_Detected === self::DETECTED_ISO_WIN_OR)
                return iconv(self::ENCODE_ISO, self::ENCODE_UTF, $this->_Content);
            elseif($this->_Detected === self::DETECTED_ISO_WIN_MIX){ // convert iso-win to utf
                for($c = '', $i = 0, $l = strlen( $this->_Chars ); $i < $l; $i++) {
                    if((bool)array_intersect([$this->_Chars[$i]], self::$_CHARS_ISO))
                        $c .= iconv(self::ENCODE_ISO, self::ENCODE_UTF, $this->_Chars[$i]);
                    else
                        $c .= iconv(self::ENCODE_WIN, self::ENCODE_UTF, $this->_Chars[$i]);
                }

                return $c;
            } elseif ($this->_Detected === self::DETECTED_UNRECOGNIZED)
                return false;
            else
                return iconv($this->_Encode, self::ENCODE_UTF, $this->_Content);
	}
}