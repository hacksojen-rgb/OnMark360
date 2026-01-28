            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
<script>
    // ১. গ্লোবাল ভেরিয়েবল (কোন ইনপুটে ডাটা যাবে তা মনে রাখার জন্য)
    var globalTargetInput = null;
    var globalTargetPreview = null;

    // ২. মিডিয়া ম্যানেজার ওপেনার ফাংশন
    function openMediaManager(inputId, previewId = null) {
        globalTargetInput = inputId;
        globalTargetPreview = previewId;
        // অবশ্যই '?popup=1' থাকতে হবে
        window.open('media.php?popup=1', 'MediaManager', 'width=1000,height=700,resizable=yes,scrollbars=yes'); 
    }

    // ৩. মিডিয়া পেজ থেকে ডাটা রিসিভ করার ফাংশন
    window.updateImageInput = function(filename, fullUrl) {
        // ফাইলের নাম ঠিক করা (uploads/ ফোল্ডার যোগ করা যদি না থাকে)
        var finalValue = filename;
        if (!finalValue.startsWith('http') && !finalValue.startsWith('uploads/')) {
            finalValue = 'uploads/' + finalValue;
        }

        // ইনপুট বক্সে ভ্যালু বসানো
        if (globalTargetInput) {
            var inputEl = document.getElementById(globalTargetInput);
            if (inputEl) inputEl.value = finalValue;
        }

        // যদি প্রিভিউ ইমেজ থাকে, সেটা আপডেট করা
        if (globalTargetPreview) {
            var imgEl = document.getElementById(globalTargetPreview);
            if (imgEl) imgEl.src = fullUrl;
        }
    }
</script>
</body>
</html>
</body>
</html>