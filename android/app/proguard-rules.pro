# Mantém a ponte JavaScript -> app (se usada no futuro)
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}
