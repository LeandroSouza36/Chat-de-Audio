package io.github.leandrosouza36.retroway;

import android.Manifest;
import android.app.AlertDialog;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.os.SystemClock;
import android.provider.Settings;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.PermissionRequest;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ProgressBar;
import android.widget.Toast;

import androidx.activity.ComponentActivity;
import androidx.activity.OnBackPressedCallback;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.Insets;
import androidx.core.splashscreen.SplashScreen;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.core.view.WindowInsetsControllerCompat;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;
import androidx.webkit.WebSettingsCompat;
import androidx.webkit.WebViewFeature;

import java.util.Arrays;

/**
 * App do Retroway: abre o site dentro de um WebView com recursos de app nativo
 * (microfone, foto de perfil, puxar para atualizar, tela sem conexão, links externos).
 */
public class MainActivity extends ComponentActivity {

    private WebView web;
    private SwipeRefreshLayout puxarAtualizar;
    private ProgressBar progresso;
    private View telaOffline;

    private boolean carregandoPrimeiraVez = true;
    private boolean erroNaPagina = false;
    private long ultimoVoltar = 0;

    private ValueCallback<Uri[]> callbackArquivo;
    private PermissionRequest pedidoMicrofone;

    // Seletor de arquivos (foto de perfil)
    private final ActivityResultLauncher<Intent> escolherArquivo =
            registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), resultado -> {
                if (callbackArquivo == null) return;
                callbackArquivo.onReceiveValue(
                        WebChromeClient.FileChooserParams.parseResult(resultado.getResultCode(), resultado.getData()));
                callbackArquivo = null;
            });

    // Permissão do microfone (gravar áudios)
    private final ActivityResultLauncher<String> pedirMicrofone =
            registerForActivityResult(new ActivityResultContracts.RequestPermission(), concedida -> {
                if (pedidoMicrofone == null) return;
                if (concedida) {
                    pedidoMicrofone.grant(new String[]{PermissionRequest.RESOURCE_AUDIO_CAPTURE});
                } else {
                    pedidoMicrofone.deny();
                    avisarMicrofoneNegado();
                }
                pedidoMicrofone = null;
            });

    @Override
    protected void onCreate(Bundle estado) {
        SplashScreen splash = SplashScreen.installSplashScreen(this);
        super.onCreate(estado);
        // Mantém a tela de abertura até a primeira página carregar (no máximo 6 segundos)
        splash.setKeepOnScreenCondition(() -> carregandoPrimeiraVez);
        new Handler(Looper.getMainLooper()).postDelayed(() -> carregandoPrimeiraVez = false, 6000);

        setContentView(R.layout.activity_main);
        configurarBordas();

        web = findViewById(R.id.web);
        puxarAtualizar = findViewById(R.id.puxarAtualizar);
        progresso = findViewById(R.id.progresso);
        telaOffline = findViewById(R.id.telaOffline);

        configurarWebView();
        configurarPuxarAtualizar();
        configurarVoltar();

        findViewById(R.id.botaoTentar).setOnClickListener(v -> {
            telaOffline.setVisibility(View.GONE);
            web.reload();
        });

        if (estado != null) {
            web.restoreState(estado);
        } else {
            web.loadUrl(urlInicial(getIntent()));
        }
    }

    // ---------- Aparência ----------

    private void configurarBordas() {
        WindowCompat.setDecorFitsSystemWindows(getWindow(), false);
        View raiz = findViewById(R.id.raiz);
        ViewCompat.setOnApplyWindowInsetsListener(raiz, (v, insets) -> {
            Insets barras = insets.getInsets(
                    WindowInsetsCompat.Type.systemBars() | WindowInsetsCompat.Type.ime() | WindowInsetsCompat.Type.displayCutout());
            v.setPadding(barras.left, barras.top, barras.right, barras.bottom);
            return WindowInsetsCompat.CONSUMED;
        });
        WindowInsetsControllerCompat controle = WindowCompat.getInsetsController(getWindow(), getWindow().getDecorView());
        controle.setAppearanceLightStatusBars(true);
        controle.setAppearanceLightNavigationBars(true);
    }

    // ---------- WebView ----------

    private void configurarWebView() {
        WebSettings s = web.getSettings();
        s.setJavaScriptEnabled(true);
        s.setDomStorageEnabled(true);
        s.setMediaPlaybackRequiresUserGesture(false);
        s.setAllowFileAccess(false);
        s.setAllowContentAccess(true);
        s.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        s.setSupportMultipleWindows(false); // links com target="_blank" abrem no próprio app
        s.setTextZoom(100);
        s.setUserAgentString(s.getUserAgentString() + " RetrowayApp/" + BuildConfig.VERSION_NAME);
        if (WebViewFeature.isFeatureSupported(WebViewFeature.ALGORITHMIC_DARKENING)) {
            WebSettingsCompat.setAlgorithmicDarkeningAllowed(s, false);
        }
        WebView.setWebContentsDebuggingEnabled(BuildConfig.DEBUG);

        CookieManager cookies = CookieManager.getInstance();
        cookies.setAcceptCookie(true);
        cookies.setAcceptThirdPartyCookies(web, true);

        web.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest pedido) {
                Uri url = pedido.getUrl();
                if (ehDoSite(url)) return false;
                abrirFora(url);
                return true;
            }

            @Override
            public void onPageStarted(WebView view, String url, android.graphics.Bitmap icone) {
                erroNaPagina = false;
                progresso.setProgress(0);
                progresso.setVisibility(View.VISIBLE);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                progresso.setVisibility(View.GONE);
                puxarAtualizar.setRefreshing(false);
                carregandoPrimeiraVez = false;
                if (!erroNaPagina) telaOffline.setVisibility(View.GONE);
                CookieManager.getInstance().flush();
            }

            @Override
            public void onReceivedError(WebView view, WebResourceRequest pedido, WebResourceError erro) {
                if (pedido.isForMainFrame()) {
                    erroNaPagina = true;
                    carregandoPrimeiraVez = false;
                    puxarAtualizar.setRefreshing(false);
                    telaOffline.setVisibility(View.VISIBLE);
                }
            }
        });

        web.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int porcentagem) {
                progresso.setProgress(porcentagem);
            }

            @Override
            public void onPermissionRequest(PermissionRequest pedido) {
                runOnUiThread(() -> tratarPermissao(pedido));
            }

            @Override
            public void onPermissionRequestCanceled(PermissionRequest pedido) {
                if (pedido == pedidoMicrofone) pedidoMicrofone = null;
            }

            @Override
            public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> callback, FileChooserParams params) {
                if (callbackArquivo != null) callbackArquivo.onReceiveValue(null);
                callbackArquivo = callback;
                try {
                    escolherArquivo.launch(params.createIntent());
                } catch (ActivityNotFoundException e) {
                    callbackArquivo = null;
                    callback.onReceiveValue(null);
                    return false;
                }
                return true;
            }
        });

        // Downloads (se houver) abrem no navegador
        web.setDownloadListener((url, agente, disposicao, tipo, tamanho) -> abrirFora(Uri.parse(url)));
    }

    private void tratarPermissao(PermissionRequest pedido) {
        boolean querMicrofone = Arrays.asList(pedido.getResources()).contains(PermissionRequest.RESOURCE_AUDIO_CAPTURE);
        if (!querMicrofone || !ehDoSite(pedido.getOrigin())) {
            pedido.deny();
            return;
        }
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.RECORD_AUDIO) == PackageManager.PERMISSION_GRANTED) {
            pedido.grant(new String[]{PermissionRequest.RESOURCE_AUDIO_CAPTURE});
        } else {
            if (pedidoMicrofone != null) pedidoMicrofone.deny();
            pedidoMicrofone = pedido;
            pedirMicrofone.launch(Manifest.permission.RECORD_AUDIO);
        }
    }

    private void avisarMicrofoneNegado() {
        if (shouldShowRequestPermissionRationale(Manifest.permission.RECORD_AUDIO)) {
            Toast.makeText(this, R.string.mic_negado, Toast.LENGTH_LONG).show();
            return;
        }
        // O usuário marcou "não perguntar de novo": leva para as configurações do app
        new AlertDialog.Builder(this)
                .setMessage(R.string.mic_negado)
                .setPositiveButton(R.string.mic_configuracoes, (d, i) -> {
                    Intent intent = new Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS,
                            Uri.fromParts("package", getPackageName(), null));
                    startActivity(intent);
                })
                .setNegativeButton(android.R.string.cancel, null)
                .show();
    }

    // ---------- Puxar para atualizar ----------

    private void configurarPuxarAtualizar() {
        puxarAtualizar.setColorSchemeResources(R.color.roxo, R.color.rosa);
        puxarAtualizar.setOnRefreshListener(() -> web.reload());
        // Só atualiza quando a página está no topo
        puxarAtualizar.setOnChildScrollUpCallback((pai, filho) -> web.getScrollY() > 0);
    }

    // ---------- Botão voltar ----------

    private void configurarVoltar() {
        getOnBackPressedDispatcher().addCallback(this, new OnBackPressedCallback(true) {
            @Override
            public void handleOnBackPressed() {
                if (telaOffline.getVisibility() != View.VISIBLE && web.canGoBack()) {
                    web.goBack();
                    return;
                }
                long agora = SystemClock.elapsedRealtime();
                if (agora - ultimoVoltar < 2000) {
                    finish();
                } else {
                    ultimoVoltar = agora;
                    Toast.makeText(MainActivity.this, R.string.sair_aviso, Toast.LENGTH_SHORT).show();
                }
            }
        });
    }

    // ---------- Links ----------

    private boolean ehDoSite(Uri url) {
        if (url == null) return false;
        String esquema = url.getScheme();
        return ("https".equalsIgnoreCase(esquema) || "http".equalsIgnoreCase(esquema))
                && BuildConfig.SITE_HOST.equalsIgnoreCase(url.getHost());
    }

    private void abrirFora(Uri url) {
        try {
            startActivity(new Intent(Intent.ACTION_VIEW, url));
        } catch (ActivityNotFoundException e) {
            Toast.makeText(this, R.string.sem_app_link, Toast.LENGTH_SHORT).show();
        }
    }

    private String urlInicial(Intent intent) {
        Uri dados = intent != null ? intent.getData() : null;
        return ehDoSite(dados) ? dados.toString() : BuildConfig.SITE_URL;
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        Uri dados = intent.getData();
        if (ehDoSite(dados)) web.loadUrl(dados.toString());
    }

    // ---------- Ciclo de vida ----------

    @Override
    protected void onSaveInstanceState(Bundle estado) {
        super.onSaveInstanceState(estado);
        web.saveState(estado);
    }

    @Override
    protected void onPause() {
        super.onPause();
        CookieManager.getInstance().flush();
    }

    @Override
    protected void onDestroy() {
        if (pedidoMicrofone != null) pedidoMicrofone.deny();
        if (callbackArquivo != null) callbackArquivo.onReceiveValue(null);
        web.destroy();
        super.onDestroy();
    }
}
