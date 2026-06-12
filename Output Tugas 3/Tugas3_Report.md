-- Analisis Transaksi Kritis Tugas 3 IAE

Dalam pengerjaan tugas 3 ini, saya memilih proses penambahan data buku baru (`POST /api/v1/books`) sebagai transaksi kritis yang akan diintegrasikan dengan sistem eksternal. Keputusan ini didasari oleh hasil analisis terhadap dampaknya pada sistem secara keseluruhan.

Dalam tugas 3 ini saya memilih proses penambahan buku baru POST /api/v1/books sebagai transaksi kritis karena proses ini memiliki dampak langsung ke seluruh sistem, bukan cuman ke service katalog aja. Tiap kali endpoint ini dipanggil, sistem akan menambahkan record baru ke database juga menambahkan nilai stock buku tersebut. 

Dalam konteks perpustakaan, karena menyangkut dengan perubahan stock buku inilah makanya transaksi ini masuk ke kategori kritis. Sesuai dengan ketentuan, tiap kali aset bertambah, sistem akan melapor ke audit pusat. Jadi sebelum data buku dianggap valid, harus mengirim datanya ke SOAP dosen dulu untuk memvalidasi transaksinya dan akan mendapatkan receipt number sebagai pencatatan yang sah.