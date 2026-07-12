const contentArea = document.getElementById("content-area");
const tanggal = document.getElementById("tanggal");

setInterval(() => {
  const now = new Date();
  tanggal.innerText = now.toLocaleString('id-ID');
}, 1000);

const infoList = [
  "Target Produksi Hari Ini: 1000 pcs",
  "Downtime Line A: 23 Menit",
  "Karyawan Terbaik Bulan Ini: Andi",
  "Meeting Safety pukul 08.00 WIB",
  "Gunakan APD Selalu di Area Produksi",
];

let index = 0;

function tampilkanInfo() {
  contentArea.innerText = infoList[index];
  index = (index + 1) % infoList.length;
}

setInterval(tampilkanInfo, 5000);
tampilkanInfo();

// Slideshow tambahan (gambar)
let slideIndex = 0;
const slideImages = ["slideshow/slide1.jpg", "slideshow/slide2.jpg", "slideshow/slide3.jpg"];
const imgTag = document.createElement("img");
imgTag.style.maxWidth = "80%";
imgTag.style.marginTop = "30px";
contentArea.appendChild(imgTag);

function showSlide() {
  imgTag.src = slideImages[slideIndex];
  slideIndex = (slideIndex + 1) % slideImages.length;
}
setInterval(showSlide, 15000);  // Ganti gambar setiap 15 detik
showSlide();