// Mobile FY Dropdown logic
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('mobileFyDropdownBtn');
    var dropdown = document.getElementById('mobileFyDropdown');
    var container = document.getElementById('mobile-fy-selector-container');
    if (btn && dropdown && container) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    // Smooth collapse animation for sidebar (animate height)
    // Targets collapse elements inside the sidebar to provide smooth open/close animation
    function enableSmoothSidebarCollapse() {
        var collapses = document.querySelectorAll('.sidebar .collapse');
        if (!collapses || collapses.length === 0) return;

        collapses.forEach(function(el){
            // ensure initial style
            el.style.overflow = 'hidden';

            el.addEventListener('show.bs.collapse', function(e){
                // start from 0 to full height
                el.style.height = '0px';
                var full = el.scrollHeight + 'px';
                // force reflow then animate
                requestAnimationFrame(function(){
                    el.style.transition = 'height 280ms ease';
                    el.style.height = full;
                });
                var onEnd = function(ev){
                    if (ev && ev.propertyName !== 'height') return;
                    el.style.height = null;
                    el.style.transition = null;
                    el.removeEventListener('transitionend', onEnd);
                };
                el.addEventListener('transitionend', onEnd);
            });

            el.addEventListener('hide.bs.collapse', function(e){
                // animate from current height to 0
                el.style.height = el.scrollHeight + 'px';
                requestAnimationFrame(function(){
                    el.style.transition = 'height 240ms ease';
                    el.style.height = '0px';
                });
                var onEndHide = function(ev){
                    if (ev && ev.propertyName !== 'height') return;
                    el.style.height = null;
                    el.style.transition = null;
                    el.removeEventListener('transitionend', onEndHide);
                };
                el.addEventListener('transitionend', onEndHide);
            });
        });
    }

    // Try to enable smoothly now; also re-run when DOM mutations occur (in case menu rendered later)
    enableSmoothSidebarCollapse();
    // Observe sidebar for added collapse nodes (safe, light observer)
    var sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        var mo = new MutationObserver(function(m){ enableSmoothSidebarCollapse(); });
        mo.observe(sidebar, { childList: true, subtree: true });
    }
});

