import http from 'k6/http';
import { check, sleep } from 'k6';
import { parseHTML } from 'k6/html';

const BASE = 'https://absensi.denatek.my.id';
const LOGIN = `${BASE}/login`;

// Set real test credentials via env vars when you run:
//   k6 run -e EMAIL=you@x.com -e PASS=secret loadtest.js
const EMAIL = __ENV.EMAIL || 'loadtest@example.com';
const PASS  = __ENV.PASS  || 'changeme';

export const options = {
  scenarios: {
    hundred_users: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '30s', target: 100 }, // ramp UP to 100 over 30s
        { duration: '1m',  target: 100 }, // hold 100 for 1 min
        { duration: '15s', target: 0 },   // ramp down
      ],
      gracefulRampDown: '10s',
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% of requests under 2s
    http_req_failed:   ['rate<0.05'],  // <5% errors
  },
};

export default function () {
  // 1) GET login page -> grab session cookie + CSRF token
  const getRes = http.get(LOGIN);
  check(getRes, { 'login page 200': (r) => r.status === 200 });

  const doc = parseHTML(getRes.body);
  // Laravel default is name="_token"; fall back to any hidden input
  let token = doc.find('input[name="_token"]').attr('value');
  if (!token) token = doc.find('form input[type="hidden"]').first().attr('value');

  // 2) POST credentials + token
  const payload = {
    email: EMAIL,
    password: PASS,
  };
  if (token) payload._token = token;

  const postRes = http.post(LOGIN, payload, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    redirects: 0, // don't follow redirect, just measure the login response
  });

  check(postRes, {
    'login responded': (r) => r.status === 200 || r.status === 302 || r.status === 422,
  });

  sleep(1); // pacing: think-time between iterations
}