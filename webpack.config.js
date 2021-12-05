const webpack = require("webpack");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");
const fs = require("fs");

const entries = {
  web: "./assets/web.js",
  bigscreen: "./assets/bigscreen.js",
  admin: "./assets/admin.js",
  profile: "./assets/profile.js",
  chat: "./assets/chat.js",
  streamchat: "./assets/streamchat.js",
  votechat: "./assets/votechat.js",
};

const entryPoints = Object.keys(entries).reduce((p, v) => {
  p[v] = entries[v];
  return p;
}, {});

const cacheGroups = Object.keys(entries).reduce(
  (p, key) => {
    p[`${key}Styles`] = {
      name: key,
      test: (m, c, entry = key) =>
        m.constructor.name === "CssModule" && recursiveIssuer(m) === entry,
      chunks: "initial",
      enforce: true,
    };
    return p;
  },
  {
    commonVendor: {
      chunks: "all",
      name: "common.vendor",
      test: /[\\/]node_modules[\\/](jquery|moment|normalize.css)[\\/]/,
      reuseExistingChunk: true,
      enforce: true,
    },
    chatVendor: {
      chunks: "all",
      name: "chat.vendor",
      test: /[\\/]node_modules[\\/](dgg-chat-gui)[\\/]/,
      reuseExistingChunk: true,
      enforce: true,
    },
  }
);

module.exports = (env, argv) => {
  return {
    optimization: {
      minimize: argv.mode === 'production',
      runtimeChunk: "single",
      splitChunks: { cacheGroups },
    },
    entry: entryPoints,
    output: {
      path: __dirname + "/static",
      publicPath: "",
      filename: "[name].[contenthash].js",
    },
    plugins: [
      new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/), // cache|flairs|emotes
      new CleanWebpackPlugin({
        cleanOnceBeforeBuildPatterns: [
          "**",
          "!cache/**",
          "!flairs/**",
          "!emotes/**",
        ],
        verbose: true,
        dry: false,
      }),
      new MiniCssExtractPlugin({ filename: "[name].[contenthash].css" }),
      new WebpackManifestPlugin(),
      {
        apply: (c) =>
          c.hooks.afterEmit.tap("webpackManifestPlugin", covertManifestJsonToPhp),
      },
    ],
    watchOptions: {
      ignored: /(node_modules)/,
    },
    module: {
      rules: [
        {
          test: /\.m?js$/,
          exclude: /(node_modules)/,
          loader: "babel-loader",
          options: { presets: ["@babel/preset-env"] },
        },
        {
          test: /\.(sa|sc|c)ss$/,
          use: [
            MiniCssExtractPlugin.loader,
            "css-loader",
            "postcss-loader",
            "sass-loader",
          ],
        },
        {
          test: /\.(eot|ttf|woff2?)$/,
          loader: "file-loader",
          options: { name: "font/[name].[ext]" },
        },
        {
          test: /fa-.*\.svg/,
          loader: "file-loader",
          options: { name: "font/[name].[ext]" },
        },
        {
          test: /\.(png|jpg|gif|svg)$/,
          exclude: /fa-.*\.svg/,
          loader: "file-loader",
          options: { name: "img/[name].[ext]" },
        },
        {
          test: /\.(html)$/,
          loader: "html-loader",
          options: { minimize: true },
        },
      ],
    },
    resolve: {
      alias: { jquery: "jquery/src/jquery" },
      extensions: [".js"],
      symlinks: false,
    },
    context: __dirname,
    devtool: argv.mode === 'development' ? 'inline-source-map' : false
  };
};

function covertManifestJsonToPhp() {
  const json = JSON.parse(
    fs.readFileSync(__dirname + "/static/manifest.json").toString("utf-8")
  );
  const data =
    `<?php\r\n// auto-generated: ${new Date().getTime()}\r\nreturn [\r\n` +
    Object.keys(json)
      .map((v) => `\t"${v}" => "` + json[v] + `"`)
      .join(",\r\n") +
    `\r\n];`;
  fs.writeFileSync(__dirname + "/config/manifest.php", data, "utf8");
  fs.unlinkSync(__dirname + "/static/manifest.json");
}

function recursiveIssuer(m) {
  if (m.issuer) {
    return recursiveIssuer(m.issuer);
  } else if (m.name) {
    return m.name;
  } else {
    return false;
  }
}
