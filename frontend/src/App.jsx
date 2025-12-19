import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Layout from './components/Layout';
import SettingsPage from './pages/SettingsPage';
import CrawlerPage from './pages/CrawlerPage';
import ScriptPage from './pages/ScriptPage';
import VoicePage from './pages/VoicePage';
import VideoPage from './pages/VideoPage';
import RenderPage from './pages/RenderPage';
import PreviewPage from './pages/PreviewPage';

export default function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          <Route path="/" element={<CrawlerPage />} />
          <Route path="/settings" element={<SettingsPage />} />
          <Route path="/script" element={<ScriptPage />} />
          <Route path="/voice" element={<VoicePage />} />
          <Route path="/video" element={<VideoPage />} />
          <Route path="/render" element={<RenderPage />} />
          <Route path="/preview" element={<PreviewPage />} />
        </Routes>
      </Layout>
    </Router>
  );
}
