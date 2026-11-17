<!--- footer --->
@guest
@php
    $footerEmail = optional($contact ?? null)->email ?: ($supportEmail ?? 'care@adwiseri.com');
    // Follow Us (social links) stays hidden until the channels are set up.
    $showSocialLinks = false;
@endphp
<div class="ds ds-footer-wrap">
  <footer class="site-footer">
    <div class="wrap">
      <div class="foot-grid">
        <div class="foot-brand">
          <a class="brand" href="{{ route('/') }}" aria-label="adwiseri home"><img src="{{ asset('web_assets/images/Style2.png') }}" alt="adwiseri" width="152" height="36" onerror="this.parentNode.classList.add('logo-fallback')"><span class="brand-text" style="color:#fff">adwiseri</span></a>
          <p>One-stop solution for Visa &amp; Immigration Consultancies with features like Cloud Storage, Users &amp; Case Management, Analytics, Reports, and much more.</p>
          <div class="foot-support">
            <svg class="icon"><use href="#i-headset"/></svg>
            <div>
              <b>Our Support team is available:</b>
              <span>Monday – Friday, 10am – 6pm GMT</span>
              <span>Email Us: <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a></span>
            </div>
          </div>
        </div>
        <div class="foot-col">
          <h4>Product</h4>
          <ul>
            <li><a href="{{ route('features') }}">Features</a></li>
            <li><a href="{{ route('membership') }}">Pricing</a></li>
            <li><a href="{{ route('get_demo') }}">Get A Demo</a></li>
            <li><a href="{{ route('login') }}">Login</a></li>
          </ul>
        </div>
        <div class="foot-col">
          <h4>Company</h4>
          <ul>
            <li><a href="{{ route('/') }}">Home</a></li>
            <li><a href="{{ route('contactus') }}">Contact Us</a></li>
            <li><a href="{{ route('affiliate.createLogin') }}">Affiliates</a></li>
            <li><a href="{{ route('Affiliates.create') }}">Join Affiliate Program</a></li>
          </ul>
        </div>
        <div class="foot-col">
          <h4>Legal</h4>
          <ul>
            <li><a href="{{ route('terms_use') }}">Terms of Use</a></li>
            <li><a href="{{ route('privacy_policy') }}">Privacy Policy</a></li>
            <li><a href="{{ route('terms_conditions') }}">GDPR</a></li>
            <li><a href="{{ route('refund_policy') }}">Cookie Notice</a></li>
          </ul>
          @if ($showSocialLinks)
          <div class="socials" aria-label="Follow us">
            <a href="#" aria-label="Facebook"><svg class="icon"><use href="#i-fb"/></svg></a>
            <a href="#" aria-label="Instagram"><svg class="icon"><use href="#i-ig"/></svg></a>
            <a href="#" aria-label="X (Twitter)"><svg class="icon"><use href="#i-tw"/></svg></a>
            <a href="#" aria-label="LinkedIn"><svg class="icon"><use href="#i-in"/></svg></a>
            <a href="#" aria-label="YouTube"><svg class="icon"><use href="#i-yt"/></svg></a>
          </div>
          @endif
        </div>
      </div>
      <div class="foot-bottom">
        <span>&copy; {{ $copyrightYears }} adwiseri. All rights reserved.</span>
        <nav aria-label="Legal"><a href="{{ route('terms_use') }}">Terms</a><a href="{{ route('privacy_policy') }}">Privacy</a><a href="{{ route('refund_policy') }}">Cookies</a></nav>
      </div>
      <div class="foot-word" aria-hidden="true">adwiseri</div>
    </div>
  </footer>
  <button type="button" class="totop" id="totop" aria-label="Back to top"><svg class="icon"><use href="#i-up"/></svg></button>
</div>
@endguest

@auth

<footer class="mt-2 last-footer">
    <p>&copy; {{ $copyrightYears }}  adwiseri.&nbsp;All rights reserved.</p>
  </footer>
  
@endauth


{{-- <footer class="mt-2 last-footer mb-0">
  <p>&copy; {{ $copyrightYears }} |  adwiseri &nbsp;&nbsp;|&nbsp;&nbsp;   </p>
</footer> --}}

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('web_assets/js/owl.carousel.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
  var local_time = new Date();
  console.log(local_time.toString());
  $(document).ready(() => {
      var defaultDataTableOptions = { aaSorting: [] };
      [
          '#subscriberTable',
          '#clientTable',
          '#associateTable',
          '#businessTable',
          '#userTable',
          '#usersTable',
          '#invoiceTable',
          '#paymentTable'
      ].forEach(function (selector) {
          var $table = $(selector);
          if ($table.length && !$.fn.DataTable.isDataTable($table)) {
              $table.DataTable(defaultDataTableOptions);
          }
      });
      // $('.dataTables_length').css('text-align','left');
      $(".localtime").attr("value",local_time.toString());
      var $planCarousel = $("#subscription-plan");
      if ($planCarousel.length) {
          var planCount = $planCarousel.children(".plan-card").length;
          if (planCount > 3) {
              // Show every plan side by side on wide screens (up to 5); slide only when they don't all fit.
              var fitsAll = function (shown) {
                  return { items: Math.min(shown, planCount), loop: planCount > shown, nav: planCount > shown, autoplay: planCount > shown };
              };
              $planCarousel.owlCarousel({
    items: 4,
    margin: 12,
    loop: true,
    nav: true,
    dots: true,
    autoplay: true,
    autoplayTimeout: 12000,
    // smartSpeed: 5000, // Smooth sliding
    responsive: {
        0: fitsAll(1),
        576: fitsAll(2),
        768: fitsAll(3),
        992: fitsAll(4),
        1200: fitsAll(5)
    }
    });
          } else {
              $planCarousel.removeClass("owl-carousel owl-theme").addClass("plans-static-row");
          }
      }
    $('.owl-carousel').owlCarousel({
                loop: true,
                margin: 10,
                nav: true,
                @php
                    $prevslide = asset('web_assets/images/navbtn.png');
                    $nextslide = asset('web_assets/images/nextnav.png');
                @endphp
                navText: [
                    "<div class='nav-btn prev-slide' style='background: url({{ $prevslide }}) no-repeat;'></div>",
                    "<div class='nav-btn next-slide' style='background: url({{ $nextslide }}) no-repeat;'></div>"
                ],
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 3
                    },
                    1000: {
                        items: 3
                    }
                }
            })


    });
</script>
<script>
 document.addEventListener('DOMContentLoaded', function () {
      const dateInputs = document.querySelectorAll('input.date, input.datepicker, input[type="date"]');

      dateInputs.forEach((input) => {
          if (input.dataset.calendarInit === '1') return;
          if (input.classList.contains('app-modal-datepicker')) return;
          if (input.closest('#myModal')) return;
          if (input.type === 'datetime-local' || input.type === 'time') return;

          const minDate = input.getAttribute('min') || null;
          const maxDate = input.getAttribute('max') || null;
          const currentValue = input.value || null;

          if (input.type === 'date') {
              input.type = 'text';
          }

          flatpickr(input, {
              dateFormat: "d-m-Y",
              altInput: false,
              defaultDate: currentValue,
              allowInput: true,
              clickOpens: true,
              disableMobile: true,
              minDate: minDate,
              maxDate: maxDate,

              // This helps flatpickr read database date format
              parseDate: function(dateStr) {
                  if (!dateStr) return null;

                  // If value is yyyy-mm-dd
                  if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                      const parts = dateStr.split('-');
                      return new Date(parts[0], parts[1] - 1, parts[2]);
                  }

                  // If value is dd-mm-yyyy
                  if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
                      const parts = dateStr.split('-');
                      return new Date(parts[2], parts[1] - 1, parts[0]);
                  }

                  return new Date(dateStr);
              }
          });

          input.dataset.calendarInit = '1';
      });
  });
</script>
<style>
  @media screen and (max-width: 767px){
    .dataTables_filter{text-align: left;}
    .client-row{padding:5px;}
  }
</style>

<script>

  // var timezone_offset_minutes = new Date().getTimezoneOffset();
  // timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;

  // Timezone difference in minutes such as 330 or -360 or 0
  // console.log(timezone_offset_minutes);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
crossorigin="anonymous"></script>

@include('partials.intl_phone_scripts')
<script src="{{ asset('web_assets/js/auto-open-action.js') }}?v=20261027a"></script>
<script src="{{ asset('web_assets/js/adwiseri-site.js') }}?v=20261117b"></script>


<!--Start of Tawk.to Script-->

<script type="text/javascript">
// var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
// (function(){
// var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
// s1.async=true;
// s1.src='https://embed.tawk.to/64b7f33dcc26a871b029690b/1h5n8s9sr';
// s1.charset='UTF-8';
// s1.setAttribute('crossorigin','*');
// s0.parentNode.insertBefore(s1,s0);
// })();





// </script>
 <script>
    $(document).ready(function () {
         // Initialize the rating value
    let ratingValue = 0;

// Handle star click event
$('#star-rating .star').on('click', function() {
    // Get the value of the clicked star
    ratingValue = $(this).data('value');

    // Set the rating in the hidden input field
    $('#rating').val(ratingValue);

    // Update star styles (to highlight selected stars)
    $('#star-rating .star').each(function() {
        if ($(this).data('value') <= ratingValue) {
            $(this).css('color', 'gold'); // Highlight stars up to the selected one
        } else {
            $(this).css('color', 'gray'); // Reset remaining stars
        }
    });
});
        $.ajax({
        url: '/get-feedback-popup',
        method: 'GET',
        success: function (subscriber) {
            console.log(subscriber.show_popup);
            if (subscriber.show_popup == true) {
                $('#subscriberId').val(subscriber.id);
                $('#feedbackModal').modal('show');
            }
        },
        error: function () {
            console.error('Error fetching feedback popup data.');
        }
    });


    $('#feedbackForm').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: '/store-feedback',
            method: 'POST',
            data: $(this).serialize(),
            beforeSend: function (xhr) {
                // Add CSRF token to the request headers
                var token = $('meta[name="csrf-token"]').attr('content');
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            },
            success: function (response) {
                $('#feedbackModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Thank you',
                    text: response.message
                });
            },
            error: function (errors) {
                Swal.fire({
                    icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: errors.responseJSON.errors.rating[0]
                });
            }
        });
    });
});

  @if (session()->has('noclient'))
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof showNoClientAlert === 'function') {
      showNoClientAlert();
    }
  });
  @endif

  </script>


<!--End of Tawk.to Script-->

@stack('scripts')

@guest
@include('partials.cookie_notice')
@endguest

</body>

</html>
