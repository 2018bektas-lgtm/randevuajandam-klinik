{{--
    Panel formlarında alan bazlı hata gösterimi.

    NEDEN: Panel yalnızca sayfanın en üstünde toplu bir hata listesi
    gösteriyordu. 900+ satırlık formlarda (profil, site ayarları, eğitim formu)
    kullanıcı hangi alanın hatalı olduğunu bulmak için yukarı kaydırıp listeyi
    okumak zorunda kalıyordu.

    YAKLAŞIM: Her formu tek tek düzenlemek yerine, mevcut `$errors` çantası
    üzerinden tüm panel formları tek noktadan işaretleniyor:
      - hatalı alana kırmızı kenarlık + `aria-invalid="true"`
      - alanın hemen altına hata mesajı
      - ilk hatalı alana otomatik odak ve kaydırma

    Böylece yeni eklenen formlar da ek iş gerektirmeden kapsanır.
--}}
@if($errors->any())
    <script>
        (function () {
            var hatalar = @json($errors->messages());

            function alanBul(ad) {
                // Laravel nokta gosterimi: "adres.il" -> "adres[il]"
                var köşeli = ad.replace(/\.(\w+)/g, '[$1]');
                return document.querySelector(
                    '[name="' + ad + '"], [name="' + köşeli + '"], [name="' + ad + '[]"]'
                );
            }

            function isaretle(alan, mesaj) {
                if (!alan || alan.dataset.hataIsaretli === '1') { return null; }
                alan.dataset.hataIsaretli = '1';

                alan.setAttribute('aria-invalid', 'true');
                alan.classList.add('border-red-400', 'ring-1', 'ring-red-200');

                var id = alan.id || ('alan-' + Math.random().toString(36).slice(2, 9));
                alan.id = id;

                var kutu = document.createElement('p');
                kutu.className = 'mt-1 text-[11px] font-medium text-red-600';
                kutu.id = id + '-hata';
                kutu.textContent = mesaj;
                alan.setAttribute('aria-describedby', kutu.id);

                var sonra = alan.closest('.form-alan') || alan.parentElement;
                if (sonra) { sonra.appendChild(kutu); }

                return alan;
            }

            document.addEventListener('DOMContentLoaded', function () {
                var ilk = null;

                Object.keys(hatalar).forEach(function (ad) {
                    var mesaj = Array.isArray(hatalar[ad]) ? hatalar[ad][0] : hatalar[ad];
                    var isaretlenen = isaretle(alanBul(ad), mesaj);
                    if (isaretlenen && !ilk) { ilk = isaretlenen; }
                });

                if (!ilk) { return; }

                // Sekmeli formlarda alan gizli olabilir; once gorunur yap
                var panel = ilk.closest('[role="tabpanel"], .sa-panel, .tab-pane');
                if (panel && panel.hidden) { panel.hidden = false; }

                var azaltilmisHareket = window.matchMedia
                    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                ilk.scrollIntoView({
                    behavior: azaltilmisHareket ? 'auto' : 'smooth',
                    block: 'center',
                });
                try { ilk.focus({ preventScroll: true }); } catch (e) {}
            });
        })();
    </script>
@endif
