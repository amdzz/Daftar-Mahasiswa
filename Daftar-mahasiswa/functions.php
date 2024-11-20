<?php 

// KONEKSI KE DATABASE
$conn = mysqli_connect("localhost", "root", "", "phpdasar");

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}


function tambah($data) {
    global $conn;
    // ambil data dari tiap elemen dalam form
    $npm = htmlspecialchars($data["npm"]);
    $nama = htmlspecialchars($data["nama"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);

    // upload gambar
    $gambar = upload();

    if( !$gambar ) {
        return false;
    }


     // query insert data
     $query = "INSERT INTO mahasiswa
                VALUES
                ('', '$npm', '$nama', '$email', '$jurusan', '$gambar')
                ";

    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}



function upload() {

    $namaFile = $_FILES['gambar']['name'];
    $ukuranFile = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmpName = $_FILES['gambar']['tmp_name'];

    // cek apakah tidak ada gambar yang diupload
    if( $error === 4 ) {
        echo "<script>
                alert('Pilih gambar terlebih dahulu!');
                </script>";

        return false;
    }

    // cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if ( !in_array($ekstensiGambar, $ekstensiGambarValid) ) {
        echo "<script>
                alert('Yang anda upload bukan gambar!');
                </script>";

        return false;
    }

    // cek jika ukurannya terlalu besar
    if( $ukuranFile > 5000000 ) {
        echo "<script>
                alert('Ukuran gambar terlalu besar!');
                </script>";

        return false;
    }

    // lolos pengecekkan, gambar siap diupload
    // generate nama gambar baru
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;


    move_uploaded_file($tmpName, 'img/' . $namaFileBaru);

    return $namaFileBaru;

}



function hapus($id){
    global $conn;
    $file = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM siswa WHERE id='$id'"));
    unlink('img/' . $file["gambar"]);
    $hapus = "DELETE FROM siswa WHERE id='$id'";

    mysqli_query($conn,$hapus);

    return mysqli_affected_rows($conn);
}


// function hapus($id) {
//     global $conn;
//     mysqli_query($conn, "DELETE FROM mahasiswa WHERE id = $id");

//     return mysqli_affected_rows($conn);
// }



function ubah($data) {
    global $conn;

    // ambil data dari tiap elemen dalam form
    $id = $data["id"];
    $npm = htmlspecialchars($data["npm"]);
    $nama = htmlspecialchars($data["nama"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);
    $gambarLama = htmlspecialchars($data["gambarLama"]);

    // cek apakah user pilih gambar baru atau tdiak
    if( $_FILES['gambar']['error'] === 4 ) {
        $gambar = $gambarLama;
    } else {
        $file = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM siswa WHERE id='$id'"));
        unlink('img/' . $file["gambar"]);
        $gambar = upload();
    }

     // query insert data
     $query = "UPDATE mahasiswa SET
                npm = '$npm',
                nama = '$nama',
                email = '$email',
                jurusan = '$jurusan',
                gambar = '$gambar'
                WHERE id = $id
                ";

    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}


function cari($keyword) {
    $query = "SELECT * FROM mahasiswa
                WHERE
                nama LIKE '%$keyword%' OR
                npm LIKE '%$keyword%' OR
                email LIKE '%$keyword%' OR
                jurusan LIKE '%$keyword%'
            ";
    
    return query($query);
}


function registrasi($data) {
    global $conn;
    
    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);


    // cek spasi
    if (trim($username) === '' || strpos($username, ' ') !== false) {
        echo "<script>
                alert('Penulisan tidak sesuai, harap jangan gunakan spasi!');
                </script>";

        return false;
    }    


    // cek username sudah ada atau belum
    $result = mysqli_query( $conn, "SELECT username FROM user WHERE username = '$username'");

    if( mysqli_fetch_assoc($result) ) {
        echo "<script>
                alert('username sudah terdaftar!');
                </script>";
        
        return false;
    }


    // cek konfirmasi password
    if( $password !== $password2 ) {
        echo "<script>
            alert('konfirmasi password tidak sesuai!');
            </script>";

        return false;
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // tambahkan user baru ke database
    mysqli_query($conn, "INSERT INTO user VALUES('', '$username', '$password')");

    return mysqli_affected_rows($conn);

}




?>