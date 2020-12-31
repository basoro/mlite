<?php

namespace Systems\Lib;

class Pagination
{
    private $_currentPage;
    private $_totalRecords;
    private $_recordsPerPage;
    private $_url;

    public function __construct($currentPage = 1, $totalRecords = 0, $recordsPerPage = 10, $url = '?page=%d')
    {
        $this->_currentPage = (int) $currentPage;
        $this->_totalRecords = (int) $totalRecords;
        $this->_recordsPerPage = (int) $recordsPerPage;
        $this->_url = $url;
    }

    public function offset()
    {
        return ($this->_currentPage - 1) * $this->_recordsPerPage;
    }

    public function getRecordsPerPage()
    {
        return $this->_recordsPerPage;
    }

    private function _totalPages()
    {
        return ceil($this->_totalRecords / $this->_recordsPerPage);
    }

    private function _previousPage()
    {
        return $this->_currentPage - 1;
    }

    private function _nextPage()
    {
        return $this->_currentPage + 1;
    }

    private function _hasPreviousPage()
    {
        return ($this->_previousPage() >= 1) ? true : false;
    }

    private function _hasNextPage()
    {
        return ($this->_nextPage() <= $this->_totalPages()) ? true : false;
    }

    public function nav($type = 'pagination', $maxLinks = 10)
    {
        if ($this->_totalPages() > 1) {
            $filename = htmlspecialchars(pathinfo($_SERVER["SCRIPT_FILENAME"], PATHINFO_BASENAME), ENT_QUOTES, "utf-8");

            $links = '<nav class="text-center">';
            $links.= '<ul class="'.$type.'">';

            if ($this->_hasPreviousPage()) {
                if ($type == 'pagination') {
                    $links.= '<li><a href="'.sprintf($this->_url, 1).'">&laquo;</a></li>';
                    $links.= '<li><a href="'.sprintf($this->_url, $this->_previousPage()).'">-</a></li>';
                } else {
                    $links.= '<li class="previous"><a href="'.sprintf($this->_url, $this->_previousPage()).'">&larr;</a></li>';
                }
            } else {
                if ($type == 'pagination') {
                    $links.= '<li class="disabled"><a href="#">&laquo;</a></li>';
                    $links.= '<li class="disabled"><a href="#">-</a></li>';
                } else {
                    $links.= '<li class="previous disabled"><a href="#">&larr;</a></li>';
                }
            }

            // Create links in the middle
            if ($type == 'pagination') {
                // Total links
                $totalLinks = ($this->_totalPages() <= $maxLinks) ? $this->_totalPages() : $maxLinks;

                // Middle link
                $middleLink = floor($totalLinks / 2);

                // Find first link and last link
                if ($this->_currentPage <= $middleLink) {
                    $lastLink = $totalLinks;
                    $firstLink = 1;
                } else {
                    if (($this->_currentPage + $middleLink) <= $this->_totalPages()) {
                        $lastLink = $this->_currentPage + $middleLink;
                    } else {
                        $lastLink = $this->_totalPages();
                    }

                    $firstLink = $lastLink - $totalLinks + 1;
                }

                for ($i = $firstLink; $i <= $lastLink; $i++) {
                    if ($this->_currentPage == $i) {
                        $links .= '<li class="active"><a href="#">' . $i . '</a></li>';
                    } else {
                        $links .= '<li><a href="'.sprintf($this->_url, $i).'">' . $i . '</a></li>';
                    }
                }
            }

            if ($this->_hasNextPage()) {
                if ($type == 'pagination') {
                    $links.= '<li><a href="'.sprintf($this->_url, $this->_nextPage()).'">+</a></li>';
                    $links.= '<li><a href="'.sprintf($this->_url, $this->_totalPages()).'">&raquo;</a></li>';
                } else {
                    $links.= '<li class="next"><a href="'.sprintf($this->_url, $this->_nextPage()).'">&rarr;</a></li>';
                }
            } else {
                if ($type == 'pagination') {
                    $links.= '<li class="disabled"><a href="#">+</a></li>';
                    $links.= '<li class="disabled"><a href="#">&raquo;</a></li>';
                } else {
                    $links.= '<li class="next disabled"><a href="#">&rarr;</a></li>';
                }
            }
            $links.='</ul>';
            $links.='</nav>';

            // Return all links of Pagination
            return $links;
        } else {
            return false;
        }
    }
}
