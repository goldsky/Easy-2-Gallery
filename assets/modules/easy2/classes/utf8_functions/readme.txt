http://forum.dklab.ru/viewtopic.php?p=91015

Contents:

   1. normal / - Scripts for the normalization of a UTF-8 from mediawiki.org
   2. Func - Class to load and execute functions that are stored in the specified folder in the form of PHP files.
   3. censure () - function tries to determine whether foul (foul, obscene words) in the html-text.
   4. html_words_highlight () - "Lights" found words for search engine results.
   5. hyphen_words () - Setting "soft" hyphenation in words.
   6. php2js () - Convert PHP scalar, array or hash to JS scalar / array / hash.
   7. strip_tags_smart () - More advanced analog strip_tags () to correctly cut tags out of html code.
   8. UTF8:: convert_from () - function to convert data of any structure of any encoding in the encoding UTF8.
   9. UTF8:: convert_from_cp1259() - Converts the text from the encoding cp1259 and cp1251, UTF-8.
  10. UTF8:: is_utf8() - Returns true if $ data is valid UTF-8 and false otherwise.
  11. UTF8:: textarea_rows() - Calculates the height of text editing (<textarea>) by value and width.
  12. UTF8:: blocks_check() - Check the text in UTF-8 charset on given ranges of the standard UNICODE. The suitable alternative to regular expressions. Check the text in UTF-8 on the specified range of the standard UNICODE. A convenient alternative to regular expressions.
  13. UTF8:: convert_from_utf16() - Convert UTF-16 / UCS-2 encoding string to UTF-8. Surrogates UTF-16 are supported! Surrogates UTF-16 are supported! 
  14. UTF8:: autoconvert_request() - encodes the values of elements of arrays $ _GET, $ _POST, $ _COOKIE, $ _REQUEST, $ _FILES from cp1251 encoding in UTF-8 if necessary.
  15. UTF8:: casecmp() - Implementation strcasecmp () function for UTF-8 encoding string.
  16. UTF8:: chr()  - Converts a UNICODE codepoint to a UTF-8 character.
  17. UTF8:: chunk_split() --  - Implementation chunk_split () function for utf-8 encoding string.
  18. UTF8:: convert_case() - Converts the case of letters in a string encoded in UTF-8
  19. UTF8:: html_entity_decode() - Convert all HTML entities to UTF-8 characters
  20. UTF8:: html_entity_encode() - Convert special UTF-8 characters to HTML entities
  21. UTF8:: ord() - Converts a UTF-8 character to a UNICODE codepoint.
  22. UTF8:: preg_match_all() - Call preg_match_all () and convert byte offsets into (UTF-8) character offsets for PREG_OFFSET_CAPTURE flag.
  23. UTF8:: str_limit() - truncated text in UTF-8 encoding to the specified length, with the last word is shown as a whole, rather than breaks in the middle.
  24. UTF8:: str_split() - Implementation str_split () function for utf-8 encoding string.
  25. UTF8:: strlen() - Implementation strlen () function for utf-8 encoding string.
  26. UTF8:: strpos() - Implementation strlen () function for utf-8 encoding string.
  27. UTF8:: strrev() - Implementation strrev () function for UTF-8 encoding string.
  28. UTF8:: substr() - Implementation substr () function for utf-8 encoding string.
  29. UTF8:: substr_replace() - Implementation substr_replace () function for utf-8 encoding string.
  30. UTF8:: ucfirst() - Converts the first character in UTF-8 in the upper register.
  31. UTF8:: ucwords() - Converts to uppercase first character of each word in a string encoded in UTF-8, the remaining characters of each word converted to lowercase.
  32. UTF8:: unescape() - function decodes the string in the format% uxxxx in the format string UTF-8
  33. UTF8:: unescape_recursive() - Recursive version utf8_unescape ()
  34. UTF8:: unescape_request() - Corrects the global arrays $ _GET, $ _POST, $ _COOKIE, $ _REQUEST, decoding the values in Unicode, encoded by the function javascript escape () ~ "% uXXXX"