<?php
echo xxtea_decrypt(xxtea_encrypt("", ""), "");
echo xxtea_decrypt(xxtea_encrypt("1", ""), "");
echo xxtea_decrypt(xxtea_encrypt("1", "1"), "1");
echo xxtea_decrypt(xxtea_encrypt("12222222222222", "2222222222222222"), "2222222222222222");
echo xxtea_decrypt(xxtea_encrypt("12222222222222", "22222222222"), "22222222222");
print_r(xxtea_info());
?>