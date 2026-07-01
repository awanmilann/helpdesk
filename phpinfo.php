   <?php phpinfo(); ?>
   ```
2. Akses file tersebut di browser.  
   **Cari “PHP Version”** (paling atas).  
   Synergy Helpdesk ini wajib PHP 5.5+ (idealnya 7.1+).

---

### **B. Coba Hash Ulang Password di Server**
Kadang, hash bcrypt yang dibuat di server berbeda OS tidak kompatibel jika phpnya outdated/memakai modulo lain.  
**Buat script hashing di HOSTING Anda:**

Buat `hash_test.php`:
```php
<?php
echo password_hash("demo123", PASSWORD_BCRYPT);
?>
```

- Buka file di browser, salin HASH yang keluar.

**Langkah selanjutnya:**
1. Copy hasil hash dari halaman tersebut.
2. Update password user di database memakai hash yg baru ini, contoh:
   ```sql
   UPDATE users SET password = 'HASIL_HASH_BARU' WHERE username = '