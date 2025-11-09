import React from 'react';
import { StyleSheet, View } from 'react-native';
import { WebView } from 'react-native-webview';

export default function HomeTab() {
  return (
    <View style={styles.container}>
      <WebView
        source={{ uri: 'https://your-chem-coursework-url.onrender.com' }}
        style={{ flex: 1 }}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
